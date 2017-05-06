<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 * @package AppBundle\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="choose_user_type")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle::index.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/faq", name="faq")
     */
    public function faqAction()
    {
        return $this->render('AppBundle::faq.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/sobre", name="about")
     */
    public function aboutAction()
    {
        return $this->render('AppBundle:About:about.html.twig');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/contato", name="contact")
     */
    public function contactAction(Request $request)
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

            $this->addFlash('success', 'Contato enviado com sucesso.');

            return $this->redirectToRoute('choose_user_type');
        }
        return $this->render('@App/Contato/contact-general.html.twig');
    }

    /**
     * @param Request $request
     * @Route("/sendforgotpassword", name="sendforgotpassword")
     * @return JsonResponse
     */
    public function sendForgotPasswordMailAction(Request $request)
    {
        try {
            $repository = 'AppBundle:User';
            $type = 1;
            $email = $request->request->get('email');
            if ($request->request->get('type') == 'restaurant') {
                $type = 2;
                $repository = 'AppBundle:Restaurant';
            }
            $user = $this->getDoctrineManager()->getRepository($repository)->findOneBy(['email' => $email]);
            if (!$user) {
                throw $this->createNotFoundException();
            }

            $this->_sendResetPasswordMail($user->getSalt(), $type, $user->getEmail());
            $return = ['success' => true, 'message' => 'E-mail enviado com sucesso.'];
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $return = ['success' => false, 'message' => 'Erro ao enviar e-mail.'];
        }

        return new JsonResponse($return);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/changepassword", name="changepassword")
     */
    public function changePasswordAction(Request $request)
    {
        $salt = $request->query->get('user');
        $type = $request->query->get('type');
        $em = $this->getDoctrine()->getManager();
        $repository = 'AppBundle:User';
        if ($type == 2) {
            $repository = 'AppBundle:Restaurant';
        }
        $user = $em->getRepository($repository)->findOneBy(['salt' => $salt]);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        if ($request->getMethod() == 'POST') {
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $request->request->get('password')));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Senha alterada com sucesso!');
            if ($type == 1) {
                return $this->redirectToRoute('user_home');
            } elseif ($type == 2) {
                return $this->redirectToRoute('restaurant_home');
            }
        }
        return $this->render('@App/resetpassword.html.twig', [
            'salt' => $salt,
            'type' => $type
        ]);
    }

    /**
     * @Route("/file/upload", name="file_upload")
     * @param Request $request
     * @return JsonResponse
     */
    public function fileUploadAction(Request $request)
    {
        try {
            $destParam = $this->getParameter('restaurant_profile_dir');
            if ($request->query->get('type') == 'user') {
                $destParam = $this->getParameter('user_profile_dir');
            }

            /** @var UploadedFile $file */
            $file = $request->files->get('fileSrc');
            if($file instanceof UploadedFile) {
                $path = $this->get('kernel')->getRootDir() . '/../web/' . $destParam;
                $file->move(
                    $path,
                    $file->getClientOriginalName()
                );
            }

            $result = ['url' => $this->get('assets.packages')->getUrl($destParam).
                $file->getClientOriginalName(), 'fileName' => $file->getClientOriginalName()];
        } catch (\Exception $e) {
            $this->get('logger')->error('Erro no upload de arquivo: '.$e->getMessage());
            $result = ['success' => false];
        }

        return new JsonResponse($result);
    }

    /**
     * Envia email para alterar senha
     * @param $email
     * @return int
     */
    protected function _sendResetPasswordMail($salt, $type, $email)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Recuperar senha - RangoCard')
            ->setFrom($this->getParameter('mailer_from'))
            ->setTo($email)
            ->setContentType('text/html')
            ->setBody(
                $this->renderView(
                    '@App/Emails/forgot-password.html.twig',
                    [
                        'salt' => $salt,
                        'type' => $type
                    ]
                )
            );
        return $this->get('mailer')->send($message);
    }
}
