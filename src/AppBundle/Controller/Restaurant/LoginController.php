<?php
namespace AppBundle\Controller\Restaurant;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\NotificationType;
use AppBundle\Entity\Restaurant;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class LoginController
 * @package AppBundle\Controller\Restaurant
 * @Route("/restaurante")
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login_restaurant")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('AppBundle:Restaurant/Login:login.html.twig', array(
                'last_username' => $authenticationUtils->getLastUsername(),
                'error'         => $authenticationUtils->getLastAuthenticationError(),
            )
        );
    }

    /**
     * @Route("/logout", name="logout_restaurant")
     */
    public function logoutAction(){}

    /**
     * @Route("/cadastro", name="restaurant_register")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        try {
            $entity = new Restaurant();
            $entity->setName($request->get('name'))
                ->setDescription($request->get('description', null))
                ->setEmail($request->get('email'))
                ->setPassword(
                    $this->get('security.password_encoder')
                        ->encodePassword($entity, $request->get('password'))
                )
                ->setCnpj($request->get('cnpj'))
                ->setFantasyName($request->get('fantasyName', null))
                ->setSite($request->get('site', null))
                ->setPhone($request->get('phone', null))
                ->setWhatsapp($request->get('whatsapp', null))
                ->setCep($request->get('cep', null))
                ->setCity($request->get('city', null))
                ->setState($request->get('state', null))
                ->setStreet($request->get('street', null))
                ->setDistrict($request->get('district', null))
                ->setPicSrc($request->get('picture-src', null));

            $em = $this->getDoctrineManager();
            $em->persist($entity);
            $em->flush();

            $users = $em->getRepository('AppBundle:User')->getUsersByCity($entity->getCity());
            foreach ($users as $user) {
                $this->get('app.notification')->sendNotificationToUser($user, NotificationType::NEW_RESTAURANT, 'Novo restaurante cadastrado na sua cidade: '.$entity->getFantasyName());
            }

            $token = new UsernamePasswordToken($entity, null, 'entity_restaurant', $entity->getRoles());
            $this->get('security.token_storage')->setToken($token);

            $result = ['success' => true, 'url' => $this->generateUrl('restaurant_home')];
        } catch (UniqueConstraintViolationException $constraintViolationException) {
            $this->get('logger')->error('Erro ao criar restaurante: '.$constraintViolationException->getMessage());
            $result = ['success' => false, 'message' => 'O e-mail já está sendo usando.'];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro ao criar restaurante: '.$e->getMessage());
            $result = ['success' => false, 'message' => 'Erro ao criar registro.'];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/editar-dados", name="restaurant_edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editUserAction(Request $request)
    {
        try {
            if ($request->getMethod() == 'POST') {
                $em = $this->getDoctrine()->getManager();
                $this->getUser()->setName($request->get('name'));
                $this->getUser()->setEmail($request->get('email'));
                if (!empty($request->get('password'))) {
                    $this->getUser()->setPassword($this->get('security.password_encoder')->encodePassword($this->getUser(), $request->get('password')));
                }

                $em->persist($this->getUser());
                $em->flush();

                $this->addFlash('success', 'Dados salvos com sucesso!');
                return $this->redirectToRoute('restaurant_home');
            }

            return $this->render('@App/Register/edit.html.twig');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('restaurant_edit');
        }
    }
}
