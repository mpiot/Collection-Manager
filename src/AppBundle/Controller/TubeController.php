<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tube;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class tubeController.
 *
 * @Route("/tube")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TubeController extends Controller
{
    /**
     * @Route("/restore/{id}", name="tube_restore")
     * @Security("is_granted('TUBE_RESTORE', tube)")
     */
    public function restoreAction(Tube $tube)
    {
        $tube->setDeleted(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($tube);
        $em->flush();

        $this->addFlash('success', 'The tube has been deleted successfully.');

        // Is it a Gmo or Wild strain ?
        if ($tube->getGmoStrain()) {
            return $this->redirectToRoute('strain_gmo_view', ['id' => $tube->getGmoStrain()->getId()]);
        } else {
            return $this->redirectToRoute('strain_wild_view', ['id' => $tube->getWildStrain()->getId()]);
        }
    }

    /**
     * @Route("/delete/{id}", name="tube_delete")
     * @Security("is_granted('TUBE_DELETE', tube)")
     */
    public function deleteAction(Tube $tube)
    {
        $tube->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($tube);
        $em->flush();

        $this->addFlash('success', 'The tube has been deleted successfully.');

        // Is it a Gmo or Wild strain ?
        if ($tube->getGmoStrain()) {
            return $this->redirectToRoute('strain_gmo_view', ['id' => $tube->getGmoStrain()->getId()]);
        } else {
            return $this->redirectToRoute('strain_wild_view', ['id' => $tube->getWildStrain()->getId()]);
        }
    }
}
