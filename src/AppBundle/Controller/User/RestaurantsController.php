<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\User;

use AppBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class RestaurantsController
 * @package AppBundle\Controller\User
 * @Route("/usuario")
 */
class RestaurantsController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/restaurantes", name="user_restaurants_list")
     */
    public function indexAction()
    {
        $em = $this->getDoctrineManager();
        $restaurants = $em->getRepository('AppBundle:Restaurant')->findRestaurantsByCity($this->getUser()->getCity());

        return $this->render('@App/User/Restaurants/index.html.twig', [
            'restaurants'   => $restaurants
        ]);
    }
}