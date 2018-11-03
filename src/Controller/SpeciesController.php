<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Controller;

use App\Entity\Species;
use App\Form\Type\SpeciesType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SpeciesController.
 *
 * @Route("/species")
 */
class SpeciesController extends Controller
{
    /**
     * @Route("/",  name="species_index", methods={"GET"})
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
     * @Route("/list",  condition="request.isXmlHttpRequest()", name="species_index_ajax", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Species::class, true, true, []);

        return $this->render('species/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="species_add", methods={"GET", "POST"})
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
     * @Route("/{slug}", name="species_view", methods={"GET"})
     * @Entity("species", class="App:Species", expr="repository.findOneWithGenusAndSynonyms(slug)")
     * @Security("is_granted('ROLE_USER')")
     */
    public function viewAction(Species $species)
    {
        if (!$species->isMainSpecies()) {
            return $this->redirectToRoute('species_view', ['slug' => $species->getMainSpecies()->getSlug()]);
        }

        $deleteForm = $this->createDeleteForm($species);

        return $this->render('species/view.html.twig', [
            'species' => $species,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="species_edit", methods={"GET", "POST"})
     * @Entity("species", class="App:Species", expr="repository.findOneWithGenus(slug)")
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
     * @Route("/{slug}", name="species_delete", methods={"DELETE"})
     * @Entity("species", class="App:Species", expr="repository.findOneWithGenus(slug)")
     * @Security("is_granted('ROLE_ADMIN') and null === species.getMainSpecies()")
     */
    public function deleteAction(Species $species, Request $request)
    {
        // Check if the species is used in strains, else redirect user
        if (!$species->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The species cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('species_view', ['slug' => $species->getSlug()]);
        }

        $form = $this->createDeleteForm($species);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($species);
            $em->flush();
        }

        $this->addFlash('success', 'The species has been deleted successfully.');

        return $this->redirectToRoute('species_index');
    }

    /**
     * Creates a form to delete a sepcies entity.
     *
     * @param Species $species The species entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Species $species): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('species_delete', ['slug' => $species->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/json/{taxid}",  condition="request.isXmlHttpRequest()", name="species_getjson", methods={"GET"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function getJsonAction($taxid)
    {
        $data = $this->get('App\Utils\TaxId')->getArray((int) $taxid);

        return new JsonResponse($data);
    }
}
