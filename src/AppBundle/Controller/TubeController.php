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
     * @Route("/{id}/delete", name="tube_delete")
     * @Security("is_granted('TUBE_DELETE', tube)")
     */
    public function deleteAction(Tube $tube, Request $request)
    {
        if (!$this->isCsrfTokenValid('deleteTube', $request->get('token'))) {
            $this->addFlash('warning', 'The token is not valid !');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($tube);
        $em->flush();

        $this->addFlash('success', 'The tube has been deleted successfully.');

        return $this->redirectToRoute('strain_view', [
            'id' => $tube->getStrain()->getId(),
            'slug' => $tube->getStrain()->getSlug(),
        ]);
    }
}
