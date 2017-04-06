<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\Restaurant;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContatoController
 * @package AppBundle\Controller\Restaurant
 * @Route("/restaurante")
 */
class ContatoController extends AbstractController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/contato", name="restaurant_contact")
     */
    public function indexAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrineManager();
            $contact = new Contact();
            $contact->setName($request->get('name'))
                ->setEmail($request->get('email'))
                ->setPhone($request->get('phone', null))
                ->setMessage($request->get('message'));

            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('user_home');
        }
        return $this->render('@App/Contato/index.html.twig');
    }
}