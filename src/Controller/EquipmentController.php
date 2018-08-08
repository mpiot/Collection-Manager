<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Entity\Group;
use App\Form\Type\EquipmentEditType;
use App\Form\Type\EquipmentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Equipment controller.
 *
 * @Route("equipment")
 */
class EquipmentController extends Controller
{
    /**
     * Lists all equipment entities.
     *
     * @Route("/", name="equipment_index", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('equipment/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list", condition="request.isXmlHttpRequest()", name="equipment_index_ajax", methods={"GET"})
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Equipment::class, true, true, [Group::class]);

        return $this->render('equipment/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * Add an equipment entity.
     *
     * @Route("/add", name="equipment_add", methods={"GET", "POST"})
     * @Security("user.isInGroup()")
     */
    public function addAction(Request $request)
    {
        $equipment = new Equipment();
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($equipment);
            $em->flush();

            $this->addFlash('success', 'The equipment has been created successfully.');

            return $this->redirectToRoute('equipment_view', ['id' => $equipment->getId(), 'slug' => $equipment->getSlug()]);
        }

        return $this->render('equipment/add.html.twig', [
            'equipment' => $equipment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a equipment entity.
     *
     * @Route("/{id}-{slug}", name="equipment_view", methods={"GET"})
     * @Security("equipment.getGroup().isMember(user)")
     */
    public function viewAction(Equipment $equipment)
    {
        $deleteForm = $this->createDeleteForm($equipment);
        $locationPath = $this->getDoctrine()->getManager()->getRepository('App:Location')->getPath($equipment->getLocation());

        return $this->render('equipment/view.html.twig', [
            'equipment' => $equipment,
            'locationPath' => $locationPath,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing equipment entity.
     *
     * @Route("/{id}-{slug}/edit", name="equipment_edit", methods={"GET", "POST"})
     * @Security("equipment.getGroup().isMember(user)")
     */
    public function editAction(Request $request, Equipment $equipment)
    {
        $form = $this->createForm(EquipmentEditType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The equipment has been edited successfully.');

            return $this->redirectToRoute('equipment_view', ['id' => $equipment->getId(), 'slug' => $equipment->getSlug()]);
        }

        return $this->render('equipment/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a equipment entity.
     *
     * @Route("/{id}-{slug}", name="equipment_delete", methods={"DELETE"})
     * @Security("equipment.getGroup().isMember(user)")
     */
    public function deleteAction(Request $request, Equipment $equipment)
    {
        $form = $this->createDeleteForm($equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($equipment);
            $em->flush();
        }

        $this->addFlash('success', 'The equipment has been deleted successfully.');

        return $this->redirectToRoute('equipment_index');
    }

    /**
     * Creates a form to delete a equipment entity.
     *
     * @param Equipment $equipment The equipment entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Equipment $equipment)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('equipment_delete', ['id' => $equipment->getId(), 'slug' => $equipment->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
