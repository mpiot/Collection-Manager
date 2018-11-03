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

use App\Entity\Product;
use App\Entity\ProductMovement;
use App\Form\Type\ProductMovementType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class product movement controller.
 *
 * @Route("/product-movement")
 */
class ProductMovementController extends AbstractController
{
    /**
     * @Route("/add/{product_id}", name="product_movement_add", methods={"GET", "POST"})
     * @Entity("product", class="App:Product", options={
     *     "mapping": {"product_id": "id"},
     * })
     * @Security("product.getGroup().isMember(user)")
     */
    public function addAction(Product $product, Request $request)
    {
        $productMovement = new ProductMovement($product);

        $form = $this->createForm(ProductMovementType::class, $productMovement)
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
            $em->persist($productMovement);
            $em->flush();

            $this->addFlash('success', 'The product movement has been added successfully.');

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('product_movement_add', [
                    'product_id' => $product->getId(),
                ]);
            }

            return $this->redirectToRoute('product_view', [
                    'id' => $product->getId(),
                    'slug' => $product->getSlug(),
                ]);
        }

        return $this->render('product_movement/add.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_movement_edit", methods={"GET", "POST"})
     * @Security("productMovement.getProduct().getGroup().isMember(user)")
     */
    public function editAction(ProductMovement $productMovement, Request $request)
    {
        $deleteForm = $this->createDeleteForm($productMovement);
        $editForm = $this->createForm(ProductMovementType::class, $productMovement);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The product movement has been edited successfully.');

            return $this->redirectToRoute('product_view', [
                'id' => $productMovement->getProduct()->getId(),
                'slug' => $productMovement->getProduct()->getSlug(),
            ]);
        }

        return $this->render('product_movement/edit.html.twig', [
            'productMovement' => $productMovement,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="product_movement_delete", methods={"DELETE"})
     * @Security("productMovement.getProduct().getGroup().isMember(user)")
     */
    public function deleteAction(ProductMovement $productMovement, Request $request)
    {
        $form = $this->createDeleteForm($productMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($productMovement);
            $em->flush();
        }

        $this->addFlash('success', 'The product movement has been deleted successfully.');

        return $this->redirectToRoute('product_view', [
            'id' => $productMovement->getProduct()->getId(),
            'slug' => $productMovement->getProduct()->getSlug(),
        ]);
    }

    /**
     * Creates a form to delete a product movement entity.
     *
     * @param ProductMovement $productMovement The product movement entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(ProductMovement $productMovement): \Symfony\Component\Form\FormInterface
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('product_movement_delete', ['id' => $productMovement->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
