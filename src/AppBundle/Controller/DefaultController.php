<?php

namespace AppBundle\Controller;

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
}
