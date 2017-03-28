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
            $userTeams = $em->getRepository('AppBundle:Team')->findAllForUser($this->getUser());
            $userProjects = $em->getRepository('AppBundle:Project')->findAllAuthorizedForCurrentUser($this->getUser());
        } else {
            $userTeams = null;
            $userProjects = null;
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'teams' => $userTeams,
            'projects' => $userProjects,
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
