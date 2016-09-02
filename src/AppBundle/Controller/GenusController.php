<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Form\GenusType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GenusController
 * @package AppBundle\Controller
 * 
 * @Route("/genus")
 */
class GenusController extends Controller
{
    /**
     * @Route("/", name="genus_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $genus = $em->getRepository('AppBundle:Genus')->findBy([], ['genus' => 'ASC']);

        return $this->render('genus/index.html.twig', array(
            'genusList' => $genus,
        ));
    }
    
    /**
     * @Route("/add", name="genus_add")
     */
    public function addAction(Request $request)
    {
        $species = new Genus();
        $form = $this->createForm(GenusType::class, $species);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($species);
            $em->flush();

            $this->addFlash('success', 'The genus has been added successfully.');

            return $this->redirectToRoute('genus_index');
        }

        return $this->render('genus/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="genus_edit")
     */
    public function editAction(Genus $genus, Request $request)
    {
        $form = $this->createForm(GenusType::class, $genus);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The genus has been edited successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('genus/edit.html.twig', array(
            'genus' => $genus,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="genus_delete")
     */
    public function deleteAction(Genus $genus, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($genus);
            $em->flush();

            $this->addFlash('success', 'The genus has been deleted successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('genus/delete.html.twig', array(
            'genus' => $genus,
            'form' => $form->createView(),
        ));
    }

}
