<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Species;
use AppBundle\Form\Type\SpeciesLimitedType;
use AppBundle\Form\Type\SpeciesType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SpeciesController.
 *
 * @Route("/species")
 */
class SpeciesController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="species_index")
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('species/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="species_index_ajax")
     * @Security("is_granted('ROLE_USER')")
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Species::class, true, true, []);

        return $this->render('species/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="species_add")
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species)
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Save & Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($species);
            $em->flush();

            $this->addFlash('success', 'The species has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'species_add'
                : 'species_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('species/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="species_view")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenusAndSynonyms"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function viewAction(Species $species)
    {
        if (!$species->isMainSpecies()) {
            return $this->redirectToRoute('species_view', ['slug' => $species->getMainSpecies()->getSlug()]);
        }

        return $this->render('species/view.html.twig', [
            'species' => $species,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="species_edit")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenus"})
     * @Security("is_granted('ROLE_ADMIN') and null === species.getMainSpecies()")
     */
    public function editAction(Species $species, Request $request)
    {
        $form = $this->createForm(SpeciesType::class, $species);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The species has been edited successfully.');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        return $this->render('species/edit.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/delete", name="species_delete")
     * @Method("POST")
     * @ParamConverter("species", options={"repository_method" = "findOneWithGenus"})
     * @Security("is_granted('ROLE_ADMIN') and null === species.getMainSpecies()")
     */
    public function deleteAction(Species $species, Request $request)
    {
        // Check if the species is used in strains, else redirect user
        if (!$species->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The species cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('species_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($species);
        $entityManager->flush();

        $this->addFlash('success', 'The species has been deleted successfully.');

        return $this->redirectToRoute('species_index');
    }

    /**
     * @Route("/json/{taxid}", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="species_getjson")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getJsonAction($taxid)
    {
        $data = $this->get('AppBundle\Utils\TaxId')->getArray((int) $taxid);

        return new JsonResponse($data);
    }
}
