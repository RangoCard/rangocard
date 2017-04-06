<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\Restaurant;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\NotificationType;
use AppBundle\Entity\Restaurant;
use AppBundle\Entity\Sale;
use AppBundle\Entity\Seal;
use AppBundle\Entity\TempUser;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPoints;
use AppBundle\Exception\ExceededPointsException;
use AppBundle\Exception\SaleExpiredException;
use AppBundle\Exception\UserNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class HomeController
 * @package AppBundle\Controller\Restaurant
 * @Route("/restaurante")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="restaurant_home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrineManager();
        $sales = $em->getRepository('AppBundle:Sale')->findSalesByRestaurant($this->getUser());

        $salesList = [];
        $oldDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2017-01-01 00:00:00');

        foreach ($sales as $sale) {
            if (!isset($salesList[$sale['id']])) {
                $salesList[$sale['id']] = [
                    'id'            => $sale['id'],
                    'name'          => $sale['name'],
                    'startDate'     => $sale['startDate'],
                    'endDate'       => $sale['endDate'],
                    'sealLimit'     => $sale['sealLimit'],
                    'numSeals'      => 0,
                    'users'         => []
                ];
            }

            $salesList[$sale['id']]['numSeals']++;

            if ($sale['userId']) {
                if (!isset($salesList[$sale['id']]['users'][$sale['userId']])) {
                    $salesList[$sale['id']]['users'][$sale['userId']] = [
                        'id'            => $sale['userId'],
                        'name'          => $sale['userName'],
                        'createdAt'     => null,
                        'numSeals'      => 0
                    ];
                }

                $salesList[$sale['id']]['users'][$sale['userId']]['numSeals']++;

                if ($sale['sealCreatedAt'] > $oldDate) {
                    $salesList[$sale['id']]['users'][$sale['userId']]['createdAt'] = $sale['sealCreatedAt'];
                    $oldDate = $sale['sealCreatedAt'];
                }
            }
        }

        usort($salesList, function($a, $b) {
            return  $b['endDate'] > $a['endDate'];
        });

        return $this->render('AppBundle:Restaurant/Home:index.html.twig', [
            'sales'     => $salesList
        ]);
    }

    /**
     * @Route("/generate-token", name="restaurant_generate_token")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function generateTokenAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $sale = $em->getRepository('AppBundle:Sale')->find($request->request->get('sale'));
            if (!$sale) {
                throw $this->createNotFoundException('Promoção não cadastrada.');
            }
            $now = new \DateTime('now');
            if ($sale->getEndDate()->format('Y-m-d H:i:s') < $now->format('Y-m-d H:i:s')) {
                throw new SaleExpiredException('Promoção expirada.');
            }
            $user = $em->getRepository('AppBundle:User')->findOneBy(['email' => $request->request->get('email')]);
            if (!$user) {
                $this->_sendMail($request->request->get('email'), $sale, $this->getUser(), 'Rangocard - você ganhou um selo', '@App/Emails/new-user.html.twig');
                $tempUser = new TempUser();
                $tempUser->setEmail($request->request->get('email'));
                $em->persist($tempUser);
                $em->flush();
                throw new UserNotFoundException('Usuário não cadastrado, um e-mail foi enviado.');
            }

            $userTotalSeals = $em->getRepository('AppBundle:User')->getTotalSealsBySale($sale, $user);
            $userTotalSeals = $userTotalSeals[1] + 1;
            if ($userTotalSeals > $sale->getSealLimit()) {
                throw new ExceededPointsException('Usuário já alcançou limite de selos da promoção.');
            }

            if ($userTotalSeals == $sale->getSealLimit()) {
                $userPoint = $em->getRepository('AppBundle:UserPoints')->findOneBy(['user' => $user, 'sale' => $sale]);
                if (!$userPoint) {
                    $userPoint = new UserPoints();
                }

                $userPoint->setPoints($userPoint->getPoints() + 1)
                    ->setUser($user)
                    ->setSale($sale);
                $em->persist($userPoint);
            }

            $seal = new Seal();
            $seal->setUser($user)
                ->setSale($sale)
                ->setRestaurant($this->getUser());

            $em->persist($seal);

            $this->get('app.notification')->sendNotificationToUser($user, NotificationType::NEW_SEAL, '+1 selo em '.$sale->getName());

            $this->_sendMail($user->getEmail(), $sale, $this->getUser(), 'Rangocard - você ganhou um selo', '@App/Emails/new-seal.html.twig');

            $return = ['success' => true, 'message' => 'Selo gerado para o usuário: '.$user->getName()];
        } catch (ExceededPointsException $e) {
            $this->get('logger')->error('Erro ao gerar selo para usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        } catch (SaleExpiredException $e) {
            $this->get('logger')->error('Erro ao gerar selo para usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        } catch (UserNotFoundException $e) {
            $this->get('logger')->error('Erro ao gerar selo para usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        } catch (NotFoundHttpException $e) {
            $this->get('logger')->error('Erro ao gerar selo para usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao gerar selo para usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => 'Erro ao gerar selo.'];
        }
        return new JsonResponse($return);
    }

    /**
     * @param Request $request
     * @Route("/clear-user-seals", name="restaurant_clear_user_seals")
     * @Method("POST")
     * @return JsonResponse
     */
    public function clearUserSealsAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $seals = $em->getRepository('AppBundle:Seal')->findSealsByUserAndSale($request->request->get('user'), $request->request->get('sale'));
            $points = $em->getRepository('AppBundle:UserPoints')->findPointsByUserAndSale($request->request->get('user'), $request->request->get('sale'));

            foreach ($seals as $seal) {
                $em->remove($seal);
            }

            foreach ($points as $point) {
                $em->remove($point);
            }

            $em->flush();

            $return = ['success' => true, 'message' => 'Selos zerados com sucesso!'];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao limpar selos do usuário: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        }

        return new JsonResponse($return);
    }

    /**
     * @param Request $request
     * @Route("/search-user", name="restaurant_search_user")
     * @Method("POST")
     * @return JsonResponse
     */
    public function searchUserAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $sale = $em->getRepository('AppBundle:Sale')->find($request->request->get('sale'));
            $users = $em->getRepository('AppBundle:User')->findUserBySale($sale, $request->request->get('search'));

            $userList = [];
            $oldDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-01 00:00:00');

            foreach ($users as $user) {
                if (!isset($userList[$user['userId']])) {
                    $userList[$user['userId']] = [
                        'id'            => $user['userId'],
                        'name'          => $user['userName'],
                        'createdAt'     => $user['sealCreatedAt'],
                        'numSeals'      => 0,
                        'saleSealLimit' => $user['sealLimit'],
                        'saleId'        => $user['id'],
                    ];
                }

                $userList[$user['userId']]['numSeals']++;

                if ($user['userId'] == $userList[$user['userId']]['id']) {
                    if ($user['sealCreatedAt'] > $oldDate) {
                        $userList[$user['userId']]['createdAt'] = $user['sealCreatedAt'];
                        $oldDate = $user['sealCreatedAt'];
                    }
                } else {
                    $oldDate = \DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-01 00:00:00');
                }
            }

            foreach ($userList as $key => $item) {
                $userList[$key]['createdAt'] = $item['createdAt']->format('d/m/Y H:i');
            }

            usort($userList, function($a, $b) {
                return  $b['createdAt'] > $a['createdAt'];
            });

            $return = ['success' => true, 'sale' => $request->request->get('sale'), 'users' => $userList];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao tentar buscar usuario: '.$e->getMessage());
            $return = ['success' => false, 'message' => $e->getMessage()];
        }
        return new JsonResponse($return);
    }

    /**
     * Envia email
     * @param $email
     * @param Sale $sale
     * @param $title
     * @param $base
     * @return int
     * @internal param User $user
     */
    protected function _sendMail($email, Sale $sale, Restaurant $restaurant, $title, $base)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($title)
            ->setFrom($this->getParameter('mailer_from'))
            ->setTo($email)
            ->setContentType('text/html')
            ->setBody(
                $this->renderView(
                    $base, [
                        'title' => $title,
                        'email' => $email,
                        'sale' => $sale,
                        'restaurant' => $restaurant
                ])
            );
        return $this->get('mailer')->send($message);
    }
}