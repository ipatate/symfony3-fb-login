<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $fb_login = $this->get('fb_sdk');
        $fb = $fb_login->create();
        $host = $this->getParameter('host');
        $loginUrl = $fb_login->redirectLoginHelper->getLoginUrl("http://$host/fb-callback/", ['email']);

        return $this->render('default/index.html.twig', ['loginUrl' => $loginUrl]);
    }

    /**
     * @Route("/email", name="email_form")
     */
    public function emailFormAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm('AppBundle\Form\UserType', $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('homepage', array('id' => $user->getId()));
        }
        return $this->render('default/form.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
                ));
    }

    /**
     * @Route("/fb-callback/", name="fb-callback")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function fbCallbackAction(Request $request)
    {
    }

    /**
     * @Route("/logout", name="logout")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function logoutAction(Request $request)
    {
    }

    /**
     * @Route("/fb-access", name="fb-access")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function fbAccessAction(Request $request)
    {
        return $this->render('default/index.html.twig');
    }
}
