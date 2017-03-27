<?php

namespace AppBundle\Controller;

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
            throw $this->createNotFoundException("This file doen't exists.");
        }

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $request->headers->set('X-Accel-Mapping', $genbankFile->getXSendfileUploadDir().'=/uploads-internal/');

        BinaryFileResponse::trustXSendfileTypeHeader();

        $response = new BinaryFileResponse($genbankFile->getAbsolutePath());
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$autoName.'-'.$name.'.gbk"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
