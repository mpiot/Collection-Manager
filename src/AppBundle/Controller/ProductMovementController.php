<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductMovement;
use AppBundle\Form\Type\ProductMovementType;
use AppBundle\Form\Type\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class brandController.
 *
 * @Route("/product-movement")
 */
class ProductMovementController extends Controller
{
    /**
     * @Route("/add/{product_id}", name="product_movement_add")
     * @ParamConverter("product", options={"mapping": {"product_id": "id"}})
     * @Security("user.hasTeam(product.getTeam())")
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
            } else {
                return $this->redirectToRoute('product_view', [
                    'id' => $product->getId(),
                ]);
            }


        }

        return $this->render('product_movement/add.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_movement_edit")
     * @Security("user.hasTeam(productMovement.getProduct().getTeam())")
     */
    public function editAction(ProductMovement $productMovement, Request $request)
    {
        $form = $this->createForm(ProductMovementType::class, $productMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The product movement has been edited successfully.');

            return $this->redirectToRoute('product_view', [
                'id'=> $productMovement->getProduct()->getId(),
            ]);
        }

        return $this->render('product_movement/edit.html.twig', [
            'productMovement' => $productMovement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="product_movement_delete")
     * @Method("POST")
     * @Security("user.hasTeam(productMovement.getProduct().getTeam())")
     */
    public function deleteAction(ProductMovement $productMovement, Request $request)
    {
        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('product_movement_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('product_view', [
                'id' => $productMovement->getProduct()->getId(),
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($productMovement);
        $em->flush();

        $this->addFlash('success', 'The product movement has been deleted successfully.');

        return $this->redirectToRoute('product_view', [
            'id' => $productMovement->getProduct()->getId(),
        ]);
    }
}
