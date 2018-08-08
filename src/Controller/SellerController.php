<?php

namespace App\Controller;

use App\Entity\Seller;
use App\Form\Type\SellerEditType;
use App\Form\Type\SellerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Storage\FileSystemStorage;

/**
 * Class sellerController.
 *
 * @Route("/seller")
 */
class SellerController extends Controller
{
    /**
     * @Route("/", options={"expose"=true}, name="seller_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $list = $this->listAction();

        return $this->render('seller/index.html.twig', [
            'list' => $list,
            'query' => $request->get('q'),
        ]);
    }

    /**
     * @Route("/list", options={"expose"=true}, condition="request.isXmlHttpRequest()",name="seller_index_ajax", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function listAction()
    {
        $results = $this->get('App\Utils\IndexFilter')->filter(Seller::class, true, true);

        return $this->render('seller/_list.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * @Route("/add", name="seller_add", methods={"GET", "POST"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
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
     * @Route("/add-ajax", name="seller_embded_add", condition="request.isXmlHttpRequest()", methods={"POST"})
     * @Security("user.isInGroup() or is_granted('ROLE_ADMIN')")
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

        return $this->render('seller/_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="seller_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function editAction(Seller $seller, Request $request)
    {
        $form = $this->createForm(SellerEditType::class, $seller);

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
     * @Route("/{slug}/delete", name="seller_delete", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction(Seller $seller, Request $request)
    {
        // If the seller is used by a product, redirect user
        if (!$seller->getProducts()->isEmpty()) {
            $this->addFlash('warning', 'The seller cannot be deleted, it\'s used by product(s).');

            return $this->redirectToRoute('seller_index');
        }

        // If the CSRF token is invalid, redirect user
        if (!$this->isCsrfTokenValid('seller_delete', $request->get('token'))) {
            $this->addFlash('warning', 'The CSRF token is invalid.');

            return $this->redirectToRoute('seller_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($seller);
        $em->flush();

        $this->addFlash('success', 'The seller has been deleted successfully.');

        return $this->redirectToRoute('seller_index');
    }

    /**
     * @Route("/{slug}/download-offer", name="seller_download_offer", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function downloadOfferAction(Seller $seller, FileSystemStorage $storage)
    {
        if (null === $seller->getOfferName()) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        // Get the absolute path of the file and the path for X-Accel-Redirect
        $filePath = $storage->resolvePath($seller, 'offerFile');
        $xSendFilePath = $storage->resolveUri($seller, 'offerFile');
        $fileName = $seller->getSlug().'.'.pathinfo($filePath)['extension'];

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
