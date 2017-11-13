<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Seller;
use AppBundle\Entity\Type;
use AppBundle\Form\Type\SellerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class sellerController.
 *
 * @Route("/seller")
 */
class SellerController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     options={"expose"=true},
     *     name="seller_index"
     * )
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction($request);

        return $this->render('seller/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route(
     *     "/list",
     *     options={"expose"=true},
     *     condition="request.isXmlHttpRequest()",
     *     name="seller_index_ajax"
     * )
     */
    public function listAction(Request $request)
    {
        $query = ('' !== $request->get('q') && null !== $request->get('q')) ? $request->get('q') : null;
        $page = (0 < (int) $request->get('p')) ? $request->get('p') : 1;

        $repositoryManager = $this->get('fos_elastica.manager.orm');
        $repository = $repositoryManager->getRepository('AppBundle:Seller');
        $elasticQuery = $repository->searchByNameQuery($query, $page);
        $sellersList = $this->get('fos_elastica.finder.app.seller')->find($elasticQuery);
        $nbResults = $this->get('fos_elastica.index.app.seller')->count($elasticQuery);

        dump($sellersList);

        $nbPages = ceil($nbResults / Seller::NUM_ITEMS);

        return $this->render('seller/_list.html.twig', [
            'sellersList' => $sellersList,
            'query' => $query,
            'page' => $page,
            'nbPages' => $nbPages,
        ]);
    }

    /**
     * @Route("/add", name="seller_add")
     */
    public function addAction(Request $request)
    {
        $seller = new Seller();
        $form = $this->createForm(SellerType::class, $seller)
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
            $em->persist($seller);
            $em->flush();

            $this->addFlash('success', 'The seller has been added successfully.');

            $nextAction = $form->get('saveAndAdd')->isClicked()
                ? 'seller_add'
                : 'seller_index';

            return $this->redirectToRoute($nextAction);
        }

        return $this->render('seller/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/embded-add", name="seller_embded_add", condition="request.isXmlHttpRequest()")
     */
    public function embdedAddAction(Request $request)
    {
        $seller = new Seller();
        $form = $this->createForm(SellerType::class, $seller, [
            'action' => $this->generateUrl('seller_embded_add'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($seller);
            $em->flush();

            // return a json response with the new type
            return new JsonResponse([
                'success' => true,
                'id' => $seller->getId(),
                'name' => $seller->getName(),
            ]);
        }

        return $this->render('seller/embded-add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="seller_edit")
     */
    public function editAction(Seller $seller, Request $request)
    {
        $form = $this->createForm(SellerType::class, $seller);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'The seller has been edited successfully.');

            return $this->redirectToRoute('seller_index');
        }

        return $this->render('seller/edit.html.twig', [
            'seller' => $seller,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="seller_delete")
     * @Method("POST")
     */
    public function deleteAction(Seller $seller, Request $request)
    {
        // If the seller is used by a product, redirect user
        if (!$seller->getProducts()->isEmpty()) {
            $this->addFlash('warning', 'The seller cannot be deleted, it\'s used by product(s).');

            return $this->redirectToRoute('seller_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('seller_delete', $request->request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('seller_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($seller);
        $em->flush();

        $this->addFlash('success', 'The seller has been deleted successfully.');

        return $this->redirectToRoute('seller_index');
    }
}
