<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Brand;
use AppBundle\Form\Type\BrandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class brandController.
 *
 * @Route("/brand")
 */
class BrandController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="brand_index")
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
     */
    public function listAction()
    {
        $results = $this->get('AppBundle\Utils\IndexFilter')->filter(Brand::class, true, true);

        return $this->render('brand/_list.html.twig', [
            'brandsList' => $results->results,
            'query' => $results->query,
            'page' => $results->page,
            'nbPages' => $results->nbPages,
        ]);
    }

    /**
     * @Route("/add", name="brand_add")
     * @Security("user.isTeamAdministrator()")
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
     * @Route("/{id}/edit", name="brand_edit")
     * @Security("user.isTeamAdministrator()")
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
     * @Route("/{id}/delete", name="brand_delete")
     * @Method("POST")
     * @Security("user.isTeamAdministrator()")
     */
    public function deleteAction(Brand $brand, Request $request)
    {
        // If the brand is used by a product, redirect user
        if (!$brand->getProducts()->isEmpty()) {
            $this->addFlash('warning', 'The brand cannot be deleted, it\'s used by product(s).');

            return $this->redirectToRoute('brand_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('brand_delete', $request->request->get('token'))) {
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
