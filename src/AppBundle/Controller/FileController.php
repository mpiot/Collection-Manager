<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Seller;
use AppBundle\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class file controller.
 *
 * @Route("/files")
 */
class FileController extends Controller
{
    /**
     * @Route("/plasmids/{team_slug}/{autoName}-{name}.gbk", name="download_plasmid")
     * @ParamConverter("team", options={"mapping": {"team_slug": "slug"}})
     * @Security("user.hasTeam(team)")
     */
    public function plasmidAction(Team $team, $autoName, $name, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $plasmid = $em->getRepository('AppBundle:Plasmid')->findOneByTeamAndNameWithFile($team, $autoName);
        $genbankFile = $plasmid->getGenBankFile();

        if (null === $genbankFile) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $genbankFile->getXSendfileUploadDir().'=/files-internal/');

        BinaryFileResponse::trustXSendfileTypeHeader();

        $response = new BinaryFileResponse($genbankFile->getAbsolutePath());
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$autoName.'-'.$name.'.gbk"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * @Route("/documents/seller-offer/{slug}.pdf", name="download_seller_offer")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function sellerOfferAction(Seller $seller, Request $request)
    {
        $file = $seller->getOfferFile();

        if (null === $file) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $file->getXSendfileUploadDir().'=/files-internal/');

        BinaryFileResponse::trustXSendfileTypeHeader();

        $response = new BinaryFileResponse($file->getAbsolutePath());
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$seller->getSlug().'.pdf"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * @Route("/documents/products/quote/{id}-{slug}", name="download_product_quote")
     * @Security("user.hasTeam(product.getTeam())")
     */
    public function productQuoteAction(Product $product, Request $request)
    {
        $file = $product->getQuoteFile();

        if (null === $file) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $file->getXSendfileUploadDir().'=/files-internal/');

        BinaryFileResponse::trustXSendfileTypeHeader();

        $response = new BinaryFileResponse($file->getAbsolutePath());
        $response->headers->set('Content-Disposition', 'attachment;filename="quote-'.$product->getSlug().'.pdf"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * @Route("/documents/products/manual/{id}-{slug}", name="download_product_manual")
     * @Security("user.hasTeam(product.getTeam())")
     */
    public function productManualAction(Product $product, Request $request)
    {
        $file = $product->getManualFile();

        if (null === $file) {
            throw $this->createNotFoundException("This file doesn't exists.");
        }

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $file->getXSendfileUploadDir().'=/files-internal/');

        BinaryFileResponse::trustXSendfileTypeHeader();

        $response = new BinaryFileResponse($file->getAbsolutePath());
        $response->headers->set('Content-Disposition', 'attachment;filename="manual-'.$product->getSlug().'.pdf"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
