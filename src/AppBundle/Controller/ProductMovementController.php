<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductMovement;
use AppBundle\Form\Type\ProductMovementType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class product movement controller.
 *
 * @Route("/product-movement")
 */
class ProductMovementController extends Controller
{
    /**
     * @Route("/add/{product_id}", name="product_movement_add")
     * @Method({"GET", "POST"})
     * @ParamConverter("product", options={"mapping": {"product_id": "id"}})
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
            } else {
                return $this->redirectToRoute('product_view', [
                    'id' => $product->getId(),
                    'slug' => $product->getSlug(),
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
     * @Method({"GET", "POST"})
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
     * @Route("/{id}/delete", name="product_movement_delete")
     * @Method("DELETE")
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
    private function createDeleteForm(ProductMovement $productMovement)
    {
        return $this->createFormBuilder(null, ['attr' => ['data-confirmation' => true]])
            ->setAction($this->generateUrl('product_movement_delete', ['id' => $productMovement->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
