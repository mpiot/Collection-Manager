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
        if (!$this->isCsrfTokenValid('restoreTube', $request->get('token'))) {
            $this->addFlash('warning', 'The token is not valid !');
        } elseif (false === $tube->getDeleted()) {
            $this->addFlash('warning', 'The tube is not deleted.');
        } else {
            $tube->setDeleted(false);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The tube has been restored successfully.');
        }

        return $this->redirectToRoute('strain_view', [
            'id' => $tube->getStrain()->getId(),
            'slug' => $tube->getStrain()->getSlug()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tube_delete")
     * @Security("is_granted('TUBE_DELETE', tube)")
     */
    public function deleteAction(Tube $tube, Request $request)
    {
        if (!$this->isCsrfTokenValid('deleteTube', $request->get('token'))) {
            $this->addFlash('warning', 'The token is not valid !');
        } elseif (true === $tube->getDeleted()) {
            $this->addFlash('warning', 'The tube is already deleted.');
        } else {
            $tube->setDeleted(true);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The tube has been deleted successfully.');
        }

        return $this->redirectToRoute('strain_view', [
            'id' => $tube->getStrain()->getId(),
            'slug' => $tube->getStrain()->getSlug()
        ]);
    }
}
