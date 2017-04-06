<?php
namespace AppBundle\Controller\User;

use AppBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HomeController
 * @package AppBundle\Controller\User
 * @Route("/usuario")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="user_home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrineManager();
        $restaurants = $em->getRepository('AppBundle:Restaurant')->findRestaurantListWithSeals($this->getUser());

        if (empty($restaurants)) {
            $restaurants = $em->getRepository('AppBundle:Restaurant')->findRestaurantList($this->getUser());
        }

        $restaurantList = [];

        foreach ($restaurants as $restaurant) {
            if (!isset($restaurantList[$restaurant['restaurantId']])) {
                $restaurantList[$restaurant['restaurantId']] = [
                    'id'                => $restaurant['restaurantId'],
                    'name'              => $restaurant['restaurantFantasyName'] ? $restaurant['restaurantFantasyName'] : $restaurant['restaurantName'],
                    'picSrc'            => $restaurant['restaurantPicSrc'],
                    'numSales'          => 0,
                    'numSeals'          => 0,
                    'numRewards'        => 0,
                    'sales'             => []
                ];
            }

            if ($restaurant['saleId']) {
                if (!isset($restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']])) {
                    $restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']] = [
                        'id'                => $restaurant['saleId'],
                        'name'              => $restaurant['saleName'],
                        'description'       => $restaurant['saleDescription'],
                        'sealLimit'         => $restaurant['sealLimit'],
                        'numSeals'          => 0,
                        'sealsLeft'         => $restaurant['sealLimit']
                    ];
                }

                $restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']]['numSeals']++;
                $restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']]['sealsLeft'] =
                    $restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']]['sealLimit'] - $restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']]['numSeals'];

                $restaurantList[$restaurant['restaurantId']]['numSales'] = count($restaurantList[$restaurant['restaurantId']]['sales']);
                $restaurantList[$restaurant['restaurantId']]['numSeals']++;
                if ($restaurantList[$restaurant['restaurantId']]['sales'][$restaurant['saleId']]['sealsLeft'] == 0) {
                    $restaurantList[$restaurant['restaurantId']]['numRewards']++;
                }
            }
        }

        return $this->render('AppBundle:User/Home:index.html.twig', [
            'restaurants'       => $restaurantList
        ]);
    }

    /**
     * @Route("/notifications", name="user_notifications")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function notificationsAction(Request $request)
    {
        try {
            $notifications = $this->getDoctrineManager()->getRepository('AppBundle:Notification')->findAllByUser($this->getUser());
            $return = ['success' => true, 'notifications' => $notifications];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao pegar notificações: '.$e->getMessage());
            $return = ['success' => false];
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/remove-notification", name="user_remove_notification")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function removeNotificationAction(Request $request)
    {
        try {
            $notification = $this->getDoctrineManager()->getRepository('AppBundle:Notification')->find($request->request->get('id'));
            $this->get('app.notification')->removeNotificationFromUser($notification, $this->getUser());
            $return = ['success' => true];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao pegar notificações: ' . $e->getMessage());
            $return = ['success' => false];
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/rest-data", name="user_fetch_rest_data")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function restData(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $restaurant = $em->getRepository('AppBundle:Restaurant')->find($request->get('id'));
            if (!$restaurant) {
                throw $this->createNotFoundException('Restaurante não encontrado.');
            }

            $data = [
                'name'              => $restaurant->getName(),
                'phone'             => $restaurant->getPhone(),
                'whatsapp'          => $restaurant->getWhatsapp(),
                'site'              => $restaurant->getSite(),
                'address'           => $restaurant->getStreet().' '.$restaurant->getNumber().', '.
                    $restaurant->getDistrict().' - '.$restaurant->getCity().' - '.$restaurant->getState().', '.$restaurant->getCep()
            ];

            $return = ['success' => true, 'restaurant' => $data];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao receber dados de restaurante: '.$e->getMessage());
            $return = ['success' => false, 'message' => 'Erro ao receber dados de restaurante.'];
        }
        return  new JsonResponse($return);
    }

    /**
     * @param Request $request
     * @Route("/current-local", name="user_current_local")
     * @Method("POST")
     * @return JsonResponse
     */
    public function currentLocalizationAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $data = $request->request->get('localization');

            $this->getUser()->setCity($data['city'])
                ->setState($data['state'])
                ->setStreet($data['street'])
                ->setDistrict($data['district']);

            $em->persist($this->getUser());
            $em->flush();

            $return = ['success' => true];
        } catch (\Exception $e) {
            $this->get('logger')->error('Error ao salvar localização: '.$e->getMessage());
            $return = ['success' => false];
        }

        return new JsonResponse($return);
    }
}