<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (null !== $this->getUser()) {
            $userGroups = $em->getRepository('AppBundle:Group')->findAllForUser($this->getUser());
        } else {
            $userGroups = null;
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'groups' => $userGroups,
        ]);
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faqAction()
    {
        return $this->render('default/faq.html.twig');
    }
}
