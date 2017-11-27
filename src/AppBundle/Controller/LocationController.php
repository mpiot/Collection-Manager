<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Form\Type\LocationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Location');

        $rootNode = $repo->findOneByName('Location');
        $locations = $repo->getNodesHierarchy($rootNode);
        $locations = $repo->buildTreeArray($locations);

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
        ]);
    }

    /**
     * @Route("/add", name="location_add")
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
     * @Route("/add-ajax", name="location_embded_add", condition="request.isXmlHttpRequest()")
     * @Method("post")
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
     * @Route("/{id}/move-up", name="location_move_up")
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
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
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
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
     * @Route("/{id}/delete", name="location_delete")
     * @Security("is_granted('ROLE_ADMIN')")
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
