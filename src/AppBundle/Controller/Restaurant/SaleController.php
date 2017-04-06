<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\Restaurant;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\NotificationType;
use AppBundle\Entity\Sale;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SaleController
 * @package AppBundle\Controller\Restaurant
 * @Route("/restaurante")
 */
class SaleController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/criar-promocao/{id}", name="restaurant_sale_form")
     */
    public function formAction(Request $request, $id = null)
    {
        $em = $this->getDoctrineManager();
        $sale = new Sale();
        $message = 'Promoção criada com sucesso!';
        if ($id) {
            $sale = $em->getRepository('AppBundle:Sale')->find($id);
            $message = 'Promoção alterada com sucesso!';
        }
        if ($request->getMethod() == 'POST') {
            $sale->setName($request->get('name'))
                ->setDescription($request->get('description'))
                ->setSealLimit($request->get('maxSeals'))
                ->setReward($request->get('reward'))
                ->setStartDate(\DateTime::createFromFormat('d/m/Y H:i', $request->get('startDate')))
                ->setEndDate(\DateTime::createFromFormat('d/m/Y H:i', $request->get('endDate')))
                ->setEnabled((bool)$request->get('enabled'))
                ->setRestaurant($this->getUser());

            $em->persist($sale);
            $em->flush();

            $users = $em->getRepository('AppBundle:User')->getUsersByCity($this->getUser()->getCity());

            foreach ($users as $user) {
                $this->get('app.notification')->sendNotificationToUser($user, NotificationType::NEW_SALE,
                    'Nova promoção do restaurante '.$this->getUser()->getFantasyName().', '.$sale->getName());
            }

            $this->addFlash('success', $message);

            return $this->redirectToRoute('restaurant_home');
        }

        return $this->render('AppBundle:Restaurant/Sale:form.html.twig', [
            'sale' => $sale
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     * @Route("/delete-sale/{id}", name="restaurant_sale_delete")
     * @Method("POST")
     */
    public function deleteAction($id)
    {
        try {
            $em = $this->getDoctrineManager();
            $em->remove($em->getRepository('AppBundle:Sale')->find($id));
            $em->flush();

            $return = ['success' => true, 'message' => 'Promoção deletada com sucesso.'];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao deletar promoção: '.$e->getMessage());
            $return = ['success' => false, 'message' => 'Erro ao deletar promoção.'];
        }
        return new JsonResponse($return);
    }
}