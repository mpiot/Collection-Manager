<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GmoStrain;
use AppBundle\Entity\WildStrain;
use AppBundle\Form\Type\GmoStrainType;
use AppBundle\Form\Type\WildStrainType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StrainController.
 *
 * @Route("/strain")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class StrainController extends Controller
{
    /**
     * @Route("/", name="strain_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $gmoStrains = $em->getRepository('AppBundle:GmoStrain')->findAllForUser($this->getUser());
        $wildStrains = $em->getRepository('AppBundle:WildStrain')->findAllForUser($this->getUser());

        // If the user have no projects
        if (!$this->getUser()->isProjectMember()) {
            $this->addFlash('warning', 'You must be a member of a project to submit a strain.');
        }

        return $this->render('strain/index.html.twig', [
            'gmoStrains' => $gmoStrains,
            'wildStrains' => $wildStrains,
        ]);
    }
}
