<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Form\BiologicalOriginCategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController
 * @package AppBundle\Controller
 * 
 * @Route("/categories")
 */
class BiologicalOriginCategoryController extends Controller
{
    /**
     * @Route("/", name="category_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:BiologicalOriginCategory')->findBy([], ['name' => 'ASC']);

        return $this->render('biological_origin_category/index.html.twig', array(
            'categories' => $categories,
        ));
    }
    
    /**
     * @Route("/add", name="category_add")
     */
    public function addAction(Request $request)
    {
        $category = new BiologicalOriginCategory();
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'The category has been added successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="category_edit")
     */
    public function editAction(BiologicalOriginCategory $category, Request $request)
    {
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The category has been edited successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/edit.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="category_delete")
     */
    public function deleteAction(BiologicalOriginCategory $category, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'The category has been deleted successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/delete.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

}