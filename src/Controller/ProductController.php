<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Product;
use App\Form\Type\ProductEditType;
use App\Form\Type\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Storage\FileSystemStorage;

/**
 * Class product controller.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/",  name="product_index", methods={"GET"})
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
     * @Route("/list",  condition="request.isXmlHttpRequest()", name="product_index_ajax", methods={"GET"})
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Product::class, true, true, [Group::class]);

        return $this->render('product/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="product_add", methods={"GET", "POST"})
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
     * @Route("/{id}-{slug}", requirements={"id": "\d+"}, name="product_view", methods={"GET"})
     * @Security("product.getGroup().isMember(user)")
     */
    public function viewAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);
        $locationPath = $this->getDoctrine()->getManager()->getRepository('App:Location')->getPath($product->getLocation());

        return $this->render('product/view.html.twig', [
            'product' => $product,
            'locationPath' => $locationPath,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}-{slug}/edit", requirements={"id": "\d+"}, name="product_edit", methods={"GET", "POST"})
     * @Security("product.getGroup().isMember(user)")
     */
    public function editAction(Product $product, Request $request)
    {
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The product has been edited successfully.');

            return $this->redirectToRoute('product_view', ['id' => $product->getId(), 'slug' => $product->getSlug()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}-{slug}/delete", name="product_delete", methods={"DELETE"})
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
    private function createDeleteForm(Product $product): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('product_delete', ['id' => $product->getId(), 'slug' => $product->getSlug()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * @Route("/order",  name="product_order", methods={"GET"})
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
     * @Route("/order/list",  condition="request.isXmlHttpRequest()", name="product_order_ajax", methods={"GET"})
     */
    public function orderListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $groupId = ('' !== $request->get('group') && null !== $request->get('group')) ? $request->get('group') : $this->getUser()->getFavoriteGroup()->getId();
        $group = $em->getRepository('App:Group')->find($groupId);

        // Check if user is in the group
        if (!$this->getUser()->hasGroup($group)) {
            throw $this->createAccessDeniedException();
        }

        $products = $em->getRepository('App:Product')->findProductsWarning($group);

        return $this->render('product/_order_list.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/{id}-{slug}/download-quote", name="product_download_quote", methods={"GET"})
     * @Route("/{id}-{slug}/download-manual", name="product_download_manual", methods={"GET"})

     * @Security("product.getGroup().isMember(user)")
     */
    public function downloadAction(Product $product, Request $request, FileSystemStorage $storage)
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
        $filePath = $storage->resolvePath($product, $fieldName);
        $xSendFilePath = $storage->resolveUri($product, $fieldName);
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
