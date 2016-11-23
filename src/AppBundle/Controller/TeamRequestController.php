<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Entity\TeamRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/team-request")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TeamRequestController extends Controller
{
    /**
     * @Route("/", name="team_request_index")
     * @Security("user.isTeamAdministrator() or is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->isGranted('ROLE_ADMIN')) {
            $requests = $em->getRepository('AppBundle:TeamRequest')->findBy(array('answer' => null));
        } else {
            $requests = $em->getRepository('AppBundle:TeamRequest')->findAdministredBy($this->getUser());
        }

        return $this->render('team_request/index.html.twig', array(
            'requests' => $requests,
        ));
    }

    /**
     * @Route("/team/{id}", name="team_request_add")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function addAction(Team $team)
    {
        if ($this->getUser()->hasRequestedTeam($team)) {
            $this->addFlash('warning', 'Already requested !');
            return $this->redirectToRoute('team_index');
        }

        if ($this->getUser()->hasTeam($team)) {
            $this->addFlash('warning', 'Already in team !');
            return $this->redirectToRoute('team_index');
        }

        $teamRequest = new TeamRequest();
        $teamRequest->setUser($this->getUser());
        $teamRequest->setTeam($team);

        $em = $this->getDoctrine()->getManager();
        $em->persist($teamRequest);
        $em->flush();

        $this->addFlash('success', 'Request ok!');
        return $this->redirectToRoute('team_index');
    }

    /**
     * @Route("/accept/{id}", name="team_request_accept")
     * @Security("user.isAdministratorOf(teamRequest.getTeam()) or is_granted('ROLE_ADMIN')")
     */
    public function acceptAction(TeamRequest $teamRequest)
    {
        if ('requested' !== $answer = $teamRequest->getAnswer()) {
            $this->addFlash('warning', 'Already aswered: '.$answer.' !');
            return $this->redirectToRoute('team_request_index');
        }

        $teamRequest->setAnswerDate(new \DateTime());
        $teamRequest->setAnswer('accepted');

        $team = $teamRequest->getTeam();
        $user = $teamRequest->getUser();

        $team->addMember($user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($team);
        $em->flush();

        $this->addFlash('success', 'User accepted');
        return $this->redirectToRoute('team_request_index');
    }

    /**
     * @Route("/decline/{id}", name="team_request_decline")
     * @Security("user.isAdministratorOf(teamRequest.getTeam()) or is_granted('ROLE_ADMIN')")
     */
    public function declineAction(TeamRequest $teamRequest)
    {
        if ('requested' !== $answer = $teamRequest->getAnswer()) {
            $this->addFlash('warning', 'Already aswered: '.$answer.' !');
            return $this->redirectToRoute('team_request_index');
        }

        $teamRequest->setAnswerDate(new \DateTime());
        $teamRequest->setAnswer('declined');

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', 'User declined !');
        return $this->redirectToRoute('team_request_index');
    }
}
