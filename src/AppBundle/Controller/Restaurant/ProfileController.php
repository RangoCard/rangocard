<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\Restaurant;

use AppBundle\Controller\AbstractController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProfileController
 * @package AppBundle\Controller\User
 * @Route("/restaurante")
 */
class ProfileController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/perfil", name="restaurant_profile")
     */
    public function profileAction()
    {
        return $this->render('AppBundle:Restaurant/Profile:profile.html.twig');
    }

    /**
     * @Route("/editar-dados", name="restaurant_edit")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editRestaurantAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            if (!empty($request->get('password'))) {
                $this->getUser()->setPassword($this->get('security.password_encoder')->encodePassword($this->getUser(), $request->get('password')));
            }
            $this->getUser()->setName($request->get('name'))
                ->setEmail($request->get('email'))
                ->setCnpj($request->get('cnpj'))
                ->setFantasyName($request->get('fantasyName', null))
                ->setDescription($request->get('description', null))
                ->setSite($request->get('site', null))
                ->setPhone($request->get('phone', null))
                ->setWhatsapp($request->get('whatsapp', null))
                ->setCep($request->get('cep', null))
                ->setCity($request->get('city', null))
                ->setState($request->get('state', null))
                ->setStreet($request->get('street', null))
                ->setDistrict($request->get('district', null))
                ->setPicSrc($request->get('picture-src', null));

            $em->persist($this->getUser());
            $em->flush();

            $this->addFlash('success', 'Dados salvos!');
            return $this->redirectToRoute('restaurant_home');
        } catch (UniqueConstraintViolationException $constraintViolationException) {
            $this->get('logger')->error('Erro ao editar cadastro do Restaurant: '.$constraintViolationException->getMessage());
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao editar cadastro de Restaurant: '.$e->getMessage());
        }

        return $this->redirectToRoute('restaurant_profile');
    }
}