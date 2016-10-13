<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Type;
use AppBundle\Form\TypeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController
 * @package AppBundle\Controller
 * 
 * @Route("/type")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TypeController extends Controller
{
    /**
     * @Route("/", name="type_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if($this->isGranted('ROLE_ADMIN')) {
            $types = $em->getRepository('AppBundle:Type')->findBy([], ['name' => 'ASC']);
        } else {
            $types = $em->getRepository('AppBundle:Type')->findAllAuthorizedForCurrentUser($this->getUser());
        }

        return $this->render('type/index.html.twig', array(
            'typesList' => $types,
        ));
    }

    /**
     * @Route("/add", name="type_add")
     * @Security("user.isInTeam() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $species = new Type();
        $form = $this->createForm(TypeType::class, $species);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($species);
            $em->flush();

            $this->addFlash('success', 'The type has been added successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="type_edit")
     * @Security("is_granted('TYPE_EDIT', type)")
     */
    public function editAction(Type $type, Request $request)
    {
        $form = $this->createForm(TypeType::class, $type);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The type has been edited successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/edit.html.twig', array(
            'type' => $type,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="type_delete")
     * @Security("is_granted('TYPE_DELETE', type)")
     */
    public function deleteAction(Type $type, Request $request)
    {
        // Check if the type is used in strains, else redirect user
        if (!$type->getGmoStrains()->isEmpty() || !$type->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The type cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('type_index');
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($type);
            $em->flush();

            $this->addFlash('success', 'The type has been deleted successfully.');

            return $this->redirectToRoute('type_index');
        }

        return $this->render('type/delete.html.twig', array(
            'type' => $type,
            'form' => $form->createView(),
        ));
    }

}
