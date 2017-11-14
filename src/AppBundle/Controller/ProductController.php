<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Form\Type\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class brandController.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="product_index")
     * @Security("user.isInTeam()")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('product/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="product_index_ajax")
     * @Security("user.isInTeam()")
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Product');
        $elasticQuery = $repository->searchByNameQuery($query, $page, $teamId, $this->getUser());
        $productsList = $this->get('fos_elastica.finder.app.product')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.product')->count($elasticQuery);

        $nbPages = ceil($nbResults / Product::NUM_ITEMS);

        return $this->render('product/_list.html.twig', [
            'productsList' => $productsList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="product_add")
     * @Security("user.isInTeam()")
     */
    public function addAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product)
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
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'The product has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'product_add'
                : 'product_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, name="product_view")
     */
    public function viewAction(Product $product)
    {
        $locationPath = $this->getDoctrine()->getManager()->getRepository('AppBundle:Location')->getPath($product->getLocation());

        return $this->render('product/view.html.twig', [
            'product' => $product,
            'locationPath' => $locationPath,
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"}, name="product_edit")
     * @Security("user.hasTeam(product.getTeam())")
     */
    public function editAction(Product $product, Request $request)
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The product has been edited successfully.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"}, name="product_delete")
     * @Method("POST")
     * @Security("user.hasTeam(product.getTeam())")
     */
    public function deleteAction(Product $product, Request $request)
    {
        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('product_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('product_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'The product has been deleted successfully.');

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/order", options={"expose"=true}, name="product_order")
     */
    public function orderAction(Request $request)
    {
        $list = $this->orderListAction($request);

        return $this->render('product/order.html.twig', [
            'list' => $list,
        ]);
    }

    /**
     * @Route("/order/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="product_order_ajax")
     * @Security("user.isInTeam()")
     */
    public function orderListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $teamId = ('' !== $request->get('team') && null !== $request->get('team')) ? $request->get('team') : $this->getUser()->getFavoriteTeam()->getId();
        $team = $em->getRepository('AppBundle:Team')->find($teamId);

        // Check if user is in the team
        if (!$this->getUser()->hasTeam($team)) {
            throw $this->createAccessDeniedException();
        }

        $products = $em->getRepository('AppBundle:Product')->findProductsWarning($team);

        return $this->render('product/_order_list.html.twig', [
            'products' => $products,
        ]);
    }
}
