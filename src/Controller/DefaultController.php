<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (null !== $this->getUser()) {
            $userGroups = $em->getRepository('App:Group')->findAllForUser($this->getUser());
        } else {
            $userGroups = null;
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'groups' => $userGroups,
        ]);
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     */
    public function faqAction()
    {
        return $this->render('default/faq.html.twig');
    }
}
