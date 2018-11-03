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

use App\Entity\Location;
use App\Form\Type\LocationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class locationController.
 *
 * @Route("/location")
 */
class LocationController extends AbstractController
{
    /**
     * @Route("/", name="location_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('App:Location');

        $rootNode = $repo->findOneByName('Location');
        $locations = $repo->getNodesHierarchy($rootNode);
        $locations = $repo->buildTreeArray($locations);

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
        ]);
    }

    /**
     * @Route("/add", name="location_add", methods={"GET", "POST"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location)
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

            // Persist and flush the new location
            $em->persist($location);
            $em->flush();

            $this->addFlash('success', 'The location has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'location_add'
                : 'location_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('location/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/add-ajax", name="location_embded_add", condition="request.isXmlHttpRequest()", methods={"POST"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function embdedAddAction(Request $request)
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location, [
            'action' => $this->generateUrl('location_embded_add'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($location);
            $em->flush();
            // return a json response with the new type
            return new JsonResponse([
                'success' => true,
                'id' => $location->getId(),
                'name' => $location->getName(),
            ]);
        }

        return $this->render('location/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/move-up", name="location_move_up", methods={"GET"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function moveUpAction(Location $location)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('App:Location');

        $repository->moveUp($location, 1);

        return $this->redirectToRoute('location_index');
    }

    /**
     * @Route("/{id}/move-down", name="location_move_down", methods={"GET"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function moveDownAction(Location $location)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('App:Location');

        $repository->moveDown($location, 1);

        return $this->redirectToRoute('location_index');
    }

    /**
     * @Route("/{id}/edit", name="location_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function editAction(Location $location, Request $request)
    {
        $form = $this->createForm(LocationType::class, $location);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Flush the location
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The location has been edited successfully.');

            return $this->redirectToRoute('location_index');
        }

        return $this->render('location/edit.html.twig', [
            'form' => $form->createView(),
            'location' => $location,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="location_delete", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Location $location, Request $request)
    {
        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('location_delete', $request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('location_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($location);
        $em->flush();

        $this->addFlash('success', 'The location has been successfully deleted.');

        return $this->redirectToRoute('location_index');
    }
}
