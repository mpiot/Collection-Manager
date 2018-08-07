<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Form\Type\BrandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class brand controller.
 *
 * @Route("/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="brand_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('brand/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()", name="brand_index_ajax")
     * @Method("GET")
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Brand::class, true, true);

        return $this->render('brand/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="brand_add")
     * @Method({"GET", "POST"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand)
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
            $em->persist($brand);
            $em->flush();

            $this->addFlash('success', 'The brand has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'brand_add'
                : 'brand_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('brand/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/add-ajax", name="brand_embded_add", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
     */
    public function embdedAddAction(Request $request)
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand, [
            'action' => $this->generateUrl('brand_embded_add'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($brand);
            $em->flush();
            // return a json response with the new type
            return new JsonResponse([
                'success' => true,
                'id' => $brand->getId(),
                'name' => $brand->getName(),
            ]);
        }

        return $this->render('brand/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="brand_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function editAction(Brand $brand, Request $request)
    {
        $form = $this->createForm(BrandType::class, $brand);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The brand has been edited successfully.');

            return $this->redirectToRoute('brand_index');
        }

        return $this->render('brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/delete", name="brand_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Brand $brand, Request $request)
    {
        // If the brand is used by a product, redirect user
        if (!$brand->getProducts()->isEmpty()) {
            $this->addFlash('warning', 'The brand cannot be deleted, it\'s used by product(s).');

            return $this->redirectToRoute('brand_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('brand_delete', $request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('brand_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($brand);
        $em->flush();

        $this->addFlash('success', 'The brand has been deleted successfully.');

        return $this->redirectToRoute('brand_index');
    }
}
