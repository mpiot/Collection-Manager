<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Form\Type\BiologicalOriginCategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController.
 *
 * @Route("/categories")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class BiologicalOriginCategoryController extends Controller
{
    /**
     * @Route("/", name="category_index")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:BiologicalOriginCategory')->findBy([], ['name' => 'ASC']);

        return $this->render('biological_origin_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/add", name="category_add")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $category = new BiologicalOriginCategory();
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'The category has been added successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/embdedAdd", name="category_embded_add", condition="request.isXmlHttpRequest()")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function embdedAddAction(Request $request)
    {
        $category = new BiologicalOriginCategory();
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category, [
            'action' => $this->generateUrl('category_embded_add'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            // return a json response with the new type
            return new JsonResponse([
                'success' => true,
                'id' => $category->getId(),
                'name'=> $category->getName(),
            ]);
        }

        return $this->render('biological_origin_category/embdedAdd.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="category_edit")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function editAction(BiologicalOriginCategory $category, Request $request)
    {
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The category has been edited successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="category_delete")
     * @Security("user.isTeamAdministrator() or user.isProjectAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(BiologicalOriginCategory $category, Request $request)
    {
        // Check if the category is used in wild strains, else redirect user
        if (!$category->getWildStrains()->isEmpty()) {
            $this->addFlash('warning', 'The category cannot be deleted, there are strains attached.');

            return $this->redirectToRoute('category_index');
        }
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'The category has been deleted successfully.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('biological_origin_category/delete.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }
}
