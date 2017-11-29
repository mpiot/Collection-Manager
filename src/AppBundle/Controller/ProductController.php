<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Group;
use AppBundle\Form\Type\ProductEditType;
use AppBundle\Form\Type\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class product controller.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="product_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('product/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="product_index_ajax")
     * @Method("GET")
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Product::class, true, true, [Group::class]);

        return $this->render('product/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="product_add")
     * @Method({"GET", "POST"})
     * @Security("user.isInGroup()")
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
     * @Method("GET")
     * @Security("product.getGroup().isMember(user)")
     */
    public function viewAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);
        $locationPath = $this->getDoctrine()->getManager()->getRepository('AppBundle:Location')->getPath($product->getLocation());

        return $this->render('product/view.html.twig', [
            'product' => $product,
            'locationPath' => $locationPath,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id": "\d+"}, name="product_edit")
     * @Method({"GET", "POST"})
     * @Security("product.getGroup().isMember(user)")
     */
    public function editAction(Product $product, Request $request)
    {
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The product has been edited successfully.');

            return $this->redirectToRoute('product_view', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}/delete", name="product_delete")
     * @Method("DELETE")
     * @Security("product.getGroup().isMember(user)")
     */
    public function deleteAction(Product $product, Request $request)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'The product has been deleted successfully.');
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a equipment entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/order", options={"expose"=true}, name="product_order")
     * @Method("GET")
     */
    public function orderAction(Request $request)
    {
        $list = $this->orderListAction($request);

        return $this->render('product/order.html.twig', [
            'list' => $list,
            'queryGroup' => $request->get('group'),
        ]);
    }

    /**
     * @Route("/order/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="product_order_ajax")
     * @Method("GET")
     */
    public function orderListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $groupId = ('' !== $request->get('group') && null !== $request->get('group')) ? $request->get('group') : $this->getUser()->getFavoriteGroup()->getId();
        $group = $em->getRepository('AppBundle:Group')->find($groupId);

        // Check if user is in the group
        if (!$this->getUser()->hasGroup($group)) {
            throw $this->createAccessDeniedException();
        }

        $products = $em->getRepository('AppBundle:Product')->findProductsWarning($group);

        return $this->render('product/_order_list.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/{id}/download-quote", name="product_download_quote")
     * @Route("/{id}/download-manual", name="product_download_manual")
     * @Method("GET")
     * @Security("product.getGroup().isMember(user)")
     */
    public function downloadAction(Product $product, Request $request)
    {
        // If user want Quote
        if ('product_download_quote' === $request->get('_route')) {
            if (null === $product->getQuoteName()) {
                throw $this->createNotFoundException("This file doesn't exists.");
            }

            $fieldName = 'quoteFile';
            $name = 'quote';
        }
        // If user want Manual
        else {
            if (null === $product->getManualName()) {
                throw $this->createNotFoundException("This file doesn't exists.");
            }

            $fieldName = 'manualFile';
            $name = 'manual';
        }

        // Get the absolute path of the file and the path for X-Accel-Redirect
        $filePath = $this->get('vich_uploader.storage')->resolvePath($product, $fieldName);
        $xSendFilePath = $this->get('vich_uploader.storage')->resolveUri($product, $fieldName);
        $fileName = $product->getSlug().'-'.$name.'.'.pathinfo($filePath)['extension'];

        // Return a Binary Response
        BinaryFileResponse::trustXSendfileTypeHeader();
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('X-Accel-Redirect', $xSendFilePath);

        return $response;
    }
}
