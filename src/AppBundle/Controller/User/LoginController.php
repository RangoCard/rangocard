<?php
namespace AppBundle\Controller\User;

use AppBundle\Entity\Seal;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPoints;
use AppBundle\Exception\ExceededPointsException;
use AppBundle\Exception\SaleExpiredException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class LoginController
 * @package AppBundle\Controller\User
 * @Route("/usuario")
 */
class LoginController extends Controller
{
    /**
     * @Route("/login", name="login_user")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('AppBundle:User/Login:login.html.twig', array(
                'last_username' => $authenticationUtils->getLastUsername(),
                'error'         => $authenticationUtils->getLastAuthenticationError(),
            )
        );
    }

    /**
     * @Route("/logout", name="logout_user")
     */
    public function logoutAction(){}

    /**
     * @Route("/cadastro", name="user_register")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function registerAction(Request $request)
    {
        try {
            $birth = \DateTime::createFromFormat('d/m/Y', $request->get('birth'));
            $entity = new User();
            $entity->setName($request->get('name'))
                ->setEmail($request->get('email'))
                ->setPassword(
                    $this->get('security.password_encoder')
                        ->encodePassword($entity, $request->get('password'))
                )
                ->setCpf($request->get('cpf'))
                ->setBirth($birth)
                ->setPhone($request->get('phone', null))
                ->setPicSrc($request->get('picture-src', null));

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $token = new UsernamePasswordToken($entity, null, 'entity_user', $entity->getRoles());
            $this->get('security.token_storage')->setToken($token);

            $result = ['success' => true, 'url' => $this->generateUrl('user_home')];
        } catch (UniqueConstraintViolationException $constraintViolationException) {
            $this->get('logger')->error('Erro ao criar usuário: '.$constraintViolationException->getMessage());
            $result = ['success' => false, 'message' => 'O e-mail já está sendo usando.'];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao criar usuário: '.$e->getMessage());
            $result = ['success' => false, 'message' => 'Erro ao criar registro.'];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/cadastro-email/{email}/{restaurant}/{sale}", name="user_register_email")
     * @param Request $request
     * @param $sale
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws SaleExpiredException
     */
    public function registerByEmail(Request $request, $email, $restaurant, $sale)
    {
        try {
            if ($request->getMethod() == 'POST') {
                $em = $this->getDoctrine()->getManager();

                $tempUser = $em->getRepository('AppBundle:TempUser')->findOneBy(['email' => $email]);
                if (!$tempUser) {
                    throw $this->createNotFoundException('Usuário não encontrado.');
                }

                $sale = $em->getRepository('AppBundle:Sale')->find($sale);
                if (!$sale) {
                    throw $this->createNotFoundException('Promoção não cadastrada.');
                }

                $restaurant = $em->getRepository('AppBundle:Restaurant')->find($restaurant);
                if (!$restaurant) {
                    throw $this->createNotFoundException('Restaurante não cadastrado.');
                }

                $now = new \DateTime('now');
                if ($sale->getEndDate()->format('Y-m-d H:i:s') < $now->format('Y-m-d H:i:s')) {
                    throw new SaleExpiredException('Promoção expirada.');
                }

                $birth = \DateTime::createFromFormat('d/m/Y', $request->get('birth'));
                $user = new User();
                $user->setName($request->get('name'))
                    ->setEmail($tempUser->getEmail())
                    ->setPassword(
                        $this->get('security.password_encoder')
                            ->encodePassword($user, $request->get('password'))
                    )
                    ->setCpf($request->get('cpf'))
                    ->setBirth($birth)
                    ->setPhone($request->get('phone', null))
                    ->setPicSrc($request->get('picture-src', null));

                $em->persist($user);

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
                    ->setRestaurant($restaurant);

                $em->persist($seal);

                $em->remove($tempUser);
                $em->flush();

                return $this->redirectToRoute('user_home');
            }

            return $this->render('@App/User/Login/register-email.html.twig', [
                'email' => $email,
                'sale'  => $sale,
                'restaurant' => $restaurant
            ]);
        } catch (SaleExpiredException $e) {
            $this->get('logger')->error('Erro no cadastro de usuário por email: '.$e->getMessage());
        } catch (ExceededPointsException $e) {
            $this->get('logger')->error('Erro no cadastro de usuário por email: '.$e->getMessage());
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro no cadastro de usuário por email: '.$e->getMessage());
        }
        return $this->redirectToRoute('user_home');
    }
}
