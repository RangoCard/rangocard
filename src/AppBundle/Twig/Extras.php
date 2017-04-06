<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Twig;

use AppBundle\Entity\Restaurant;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class Extras extends \Twig_Extension
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;

    /**
     * Extras constructor.
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isRestaurant', array($this, 'isRestaurant')),
            new \Twig_SimpleFunction('isUser', array($this, 'isUser')),
        );
    }

    public function isRestaurant()
    {
        if ($this->getUser() instanceof Restaurant) {
            return true;
        }
        return false;
    }

    public function isUser()
    {
        if ($this->getUser() instanceof User) {
            return true;
        }
        return false;
    }

    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    public function getName()
    {
        return 'extras_extension';
    }
}