<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Restaurant;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AbstractController extends Controller
{
    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function getDoctrineManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function isRestaurant()
    {
        if ($this->getUser() instanceof Restaurant) {
            return true;
        }
        return false;
    }

    protected function isUser()
    {
        if ($this->getUser() instanceof User) {
            return true;
        }
        return false;
    }
}