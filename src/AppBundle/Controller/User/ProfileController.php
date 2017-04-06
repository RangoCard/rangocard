<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Controller\User;

use AppBundle\Controller\AbstractController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProfileController
 * @package AppBundle\Controller\User
 * @Route("/usuario")
 */
class ProfileController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/perfil", name="user_profile")
     */
    public function profileAction()
    {
        return $this->render('AppBundle:User/Profile:profile.html.twig');
    }

    /**
     * @Route("/editar-dados", name="user_edit")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editUserAction(Request $request)
    {
        try {
            $em = $this->getDoctrineManager();
            $birth = \DateTime::createFromFormat('d/m/Y', $request->get('birth'));
            if (!empty($request->get('password'))) {
                $this->getUser()->setPassword($this->get('security.password_encoder')->encodePassword($this->getUser(), $request->get('password')));
            }
            $this->getUser()->setName($request->get('name'))
                ->setEmail($request->get('email'))
                ->setCpf($request->get('cpf'))
                ->setBirth($birth)
                ->setPhone($request->get('phone', null))
                ->setCep($request->get('cep', null))
                ->setCity($request->get('city', null))
                ->setState($request->get('state', null))
                ->setStreet($request->get('street', null))
                ->setDistrict($request->get('district', null))
                ->setPicSrc($request->get('picture-src', null));

            $em->persist($this->getUser());
            $em->flush();

            $this->addFlash('success', 'Dados salvos!');
            return $this->redirectToRoute('user_home');
        } catch (UniqueConstraintViolationException $constraintViolationException) {
            $this->get('logger')->error('Erro ao editar cadastro de User: '.$constraintViolationException->getMessage());
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao editar cadastro de User: '.$e->getMessage());
        }

        return $this->redirectToRoute('user_profile');
    }
}