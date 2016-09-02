<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Type;
use AppBundle\Form\TypeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController
 * @package AppBundle\Controller
 * 
 * @Route("/type")
 */
class TypeController extends Controller
{
    /**
     * @Route("/", name="type_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $types = $em->getRepository('AppBundle:Type')->findBy([], ['name' => 'ASC']);

        return $this->render('type/index.html.twig', array(
            'typesList' => $types,
        ));
    }
    
    /**
     * @Route("/add", name="type_add")
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
     */
    public function deleteAction(Type $type, Request $request)
    {
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
