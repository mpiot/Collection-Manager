<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\SpeciesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SpeciesController.
 *
 * @Route("/species")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class SpeciesController extends Controller
{
    /**
     * @Route("/", name="species_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $species = $em->getRepository('AppBundle:Species')->findAllWithGenus();

        return $this->render('species/index.html.twig', array(
            'speciesList' => $species,
        ));
    }

    /**
     * @Route("/add", name="species_add")
     */
    public function addAction(Request $request)
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($species);
            $em->flush();

            $this->addFlash('success', 'The species has been added successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="species_edit")
     * @ParamConverter("species", class="AppBundle:Species", options={
     *     "repository_method" = "findOneWithGenus"
     * })
     */
    public function editAction(Species $species, Request $request)
    {
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The species has been edited successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/edit.html.twig', array(
            'species' => $species,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="species_delete")
     * @ParamConverter("species", class="AppBundle:Species", options={
     *     "repository_method" = "findOneWithGenus"
     * })
     */
    public function deleteAction(Species $species, Request $request)
    {
        // Check if the species is used in strains, else redirect user
        if (!$species->getGmoStrains()->isEmpty() || !$species->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The species cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('species_index');
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($species);
            $em->flush();

            $this->addFlash('success', 'The species has been deleted successfully.');

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/delete.html.twig', array(
            'species' => $species,
            'form' => $form->createView(),
        ));
    }
}
