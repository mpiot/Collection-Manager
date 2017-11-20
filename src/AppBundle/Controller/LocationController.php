<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Form\Type\LocationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class locationController.
 *
 * @Route("/location")
 */
class LocationController extends Controller
{
    /**
     * @Route("/", name="location_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Location');
        $location = $repo->findOneByName('Location');

        $options = [
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
            'nodeDecorator' => function ($node) {
                $label = $node['name'];

                if (0 !== $node['lvl']) {
                    $moveUpUrl = $this->generateUrl('location_move_up', [
                        'id' => $node['id'],
                    ]);

                    $moveDownUrl = $this->generateUrl('location_move_down', [
                        'id' => $node['id'],
                    ]);

                    $label .= ' <a class="btn btn-default btn-xs" href="'.$moveUpUrl.'"><span class="fa fa-arrow-up"></span></a><a class="btn btn-default btn-xs" href="'.$moveDownUrl.'"><span class="fa fa-arrow-down"></span></a>';
                }

                $editUrl = $this->generateUrl('location_edit', [
                    'id' => $node['id'],
                ]);
                $label .= ' <a class="btn btn-warning btn-xs" href="'.$editUrl.'">Edit</a>';

                $deleteUrl = $this->generateUrl('location_delete', [
                    'id' => $node['id'],
                ]);
                $label .= ' <a class="btn btn-danger btn-xs" href="'.$deleteUrl.'">Delete</a>';

                return $label;
            },
        ];
        $locations = $em->getRepository('AppBundle:Location')->childrenHierarchy(
            $location, // Starting from root nodes
            false, // true: load all children, false: only direct
            $options
        );

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
        ]);
    }

    /**
     * @Route("/add", name="location_add")
     * @Security("user.isGroupAdministrator()")
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
     * @Route("/{id}/move-up", name="location_move_up")
     * @Security("user.isGroupAdministrator()")
     */
    public function moveUpAction(Location $location)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Location');

        $repository->moveUp($location, 1);

        return $this->redirectToRoute('location_index');
    }

    /**
     * @Route("/{id}/move-down", name="location_move_down")
     * @Security("user.isGroupAdministrator()")
     */
    public function moveDownAction(Location $location)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Location');

        $repository->moveDown($location, 1);

        return $this->redirectToRoute('location_index');
    }

    /**
     * @Route("/{id}/edit", name="location_edit")
     * @Security("user.isGroupAdministrator()")
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
     * @Route("/{id}/delete", name="location_delete")
     * @Security("user.isGroupAdministrator()")
     */
    public function deleteAction(Location $location, Request $request)
    {
        $form = $this->createFormBuilder()
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($location);
            $em->flush();

            $this->addFlash('success', 'The location has been successfully deleted.');

            return $this->redirectToRoute('location_index');
        }

        return $this->render('location/delete.html.twig', [
            'form' => $form->createView(),
            'location' => $location,
        ]);
    }
}
