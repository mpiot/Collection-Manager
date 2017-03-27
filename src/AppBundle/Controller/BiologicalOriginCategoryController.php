<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BiologicalOriginCategory;
use AppBundle\Form\Type\BiologicalOriginCategoryEditType;
use AppBundle\Form\Type\BiologicalOriginCategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class typeController.
 *
 * @Route("/categories")
 */
class BiologicalOriginCategoryController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     options={"expose"=true},
     *     name="category_index"
     * )
     * @Security("user.isInTeam()")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('biological_origin_category/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="category_index_ajax"
     * )
     * @Security("user.isInTeam()")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:BiologicalOriginCategory');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $teamId, $this->getUser());
        $categoryList = $this->get('fos_elastica.finder.app.biologicalorigincategory')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.biologicalorigincategory')->count($elasticQuery);

        $nbPages = ceil($nbResults / BiologicalOriginCategory::NUM_ITEMS);

        return $this->render('biological_origin_category/_list.html.twig', [
            'categoryList' => $categoryList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="category_add")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request)
    {
        $category = new BiologicalOriginCategory();
        $form = $this->createForm(BiologicalOriginCategoryType::class, $category)
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-first',
                ],
            ])
            ->add('saveAndAdd', SubmitType::class, [
                'label' => 'Save and Add',
                'attr' => [
                    'data-btn-group' => 'btn-group',
                    'data-btn-position' => 'btn-last',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'The category has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'category_add'
                : 'category_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('biological_origin_category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/embdedAdd", name="category_embded_add", condition="request.isXmlHttpRequest()")
     * @Security("user.isInTeam()")
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
                'name' => $category->getName(),
            ]);
        }

        return $this->render('biological_origin_category/embdedAdd.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="category_edit")
     * @Security("is_granted('CATEGORY_EDIT', category)")
     */
    public function editAction(BiologicalOriginCategory $category, Request $request)
    {
        $form = $this->createForm(BiologicalOriginCategoryEditType::class, $category);

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
     * @Route("/{id}/delete", name="category_delete")
     * @Method("POST")
     * @Security("is_granted('CATEGORY_DELETE', category)")
     */
    public function deleteAction(BiologicalOriginCategory $category, Request $request)
    {
        // If the type is used by strains, redirect user
        if (!$category->getStrains()->isEmpty()) {
            $this->addFlash('warning', 'The category cannot be deleted, it\'s used in strain(s).');

            return $this->redirectToRoute('category_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('biological_origin_category_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('category_index');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'The category has been deleted successfully.');

        return $this->redirectToRoute('category_index');
    }
}
