<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tube;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

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
    public function restoreAction(Tube $tube, Request $request)
    {
        if ($this->isCsrfTokenValid('restoreTube-' . $tube->getId(), $request->get('token'))) {
            // If the tube is no deleted, return an error message
            if (false === $tube->getDeleted()) {
                $this->addFlash('warning', 'The tube is not deleted.');
            } else {
                $tube->setDeleted(false);

                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('success', 'The tube has been restored successfully.');
            }
        } else {
            $this->addFlash('warning', 'The token is not valid !');
        }

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
    public function deleteAction(Tube $tube, Request $request)
    {
        if ($this->isCsrfTokenValid('deleteTube-'.$tube->getId(), $request->get('token'))) {
            // If the tube is already deleted, return an error message
            if (true === $tube->getDeleted()) {
                $this->addFlash('warning', 'The tube is already deleted.');
            } else {
                $tube->setDeleted(true);

                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('success', 'The tube has been deleted successfully.');
            }
        } else {
            $this->addFlash('warning', 'The token is not valid !');
        }

        // Is it a Gmo or Wild strain ?
        if ($tube->getGmoStrain()) {
            return $this->redirectToRoute('strain_gmo_view', ['id' => $tube->getGmoStrain()->getId()]);
        } else {
            return $this->redirectToRoute('strain_wild_view', ['id' => $tube->getWildStrain()->getId()]);
        }
    }
}
