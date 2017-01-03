<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Primer;
use AppBundle\Form\Type\PrimerEditType;
use AppBundle\Form\Type\PrimerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class primerController.
 *
 * @Route("/primer")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PrimerController extends Controller
{
    /**
     * @Route("/", name="primer_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $primers = $em->getRepository('AppBundle:Primer')->findAllForUser($this->getUser());

        return $this->render('primer/index.html.twig', [
            'primers' => $primers,
        ]);
    }

    /**
     * @Route("/view/{id}", name="primer_view")
     * @Security("is_granted('PRIMER_VIEW', primer)")
     */
    public function viewAction(Primer $primer)
    {
        return $this->render('primer/view.html.twig', [
            'primer' => $primer,
        ]);
    }

    /**
     * @Route("/add", name="primer_add")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request)
    {
        $primer = new Primer();
        $form = $this->createForm(PrimerType::class, $primer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($primer);
            $em->flush();

            $this->addFlash('success', 'The primer has been added successfully.');

            return $this->redirectToRoute('primer_index');
        }

        return $this->render('primer/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="primer_edit")
     * @Security("is_granted('PRIMER_EDIT', primer)")
     */
    public function editAction(Primer $primer, Request $request)
    {
        $form = $this->createForm(PrimerEditType::class, $primer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The primer has been edited successfully.');

            return $this->redirectToRoute('primer_index');
        }

        return $this->render('primer/edit.html.twig', [
            'primer' => $primer,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="primer_delete")
     * @Security("is_granted('PRIMER_DELETE', primer)")
     */
    public function deleteAction(Primer $primer, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($primer);
            $em->flush();

            $this->addFlash('success', 'The primer has been deleted successfully.');

            return $this->redirectToRoute('primer_index');
        }

        return $this->render('primer/delete.html.twig', [
            'primer' => $primer,
            'form'   => $form->createView(),
        ]);
    }
}
