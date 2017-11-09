<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Team;
use AppBundle\Entity\TeamRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/team-request")
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class TeamRequestController extends Controller
{
    /**
     * @Route("/", name="team_request_index")
     * @Security("user.isTeamAdministrator()")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $requests = $em->getRepository('AppBundle:TeamRequest')->findAdministredBy($this->getUser());

        return $this->render('team_request/index.html.twig', [
            'requests' => $requests,
        ]);
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

        $this->get('AppBundle\Utils\Mailer')->sendTeamRequestConfirmation($teamRequest);
        $this->get('AppBundle\Utils\Mailer')->sendTeamRequestNotification($teamRequest);

        $this->addFlash('success', 'Your request has been sent successfully ! You\'ll receive a mail when an administrator will have answered to your request.');

        return $this->redirectToRoute('team_index');
    }

    /**
     * @Route("/accept/{id}", name="team_request_accept")
     * @Security("user.isAdministratorOf(teamRequest.getTeam())")
     */
    public function acceptAction(TeamRequest $teamRequest, Request $request)
    {
        if ($this->isCsrfTokenValid('accept-'.$teamRequest->getId(), $request->get('token'))) {
            if ('requested' === $answer = $teamRequest->getAnswer()) {
                $teamRequest->setAnswerDate(new \DateTime());
                $teamRequest->setAnswer('accepted');

                $team = $teamRequest->getTeam();
                $user = $teamRequest->getUser();

                $team->addMember($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($team);
                $em->flush();

                $this->get('AppBundle\Utils\Mailer')->sendTeamRequestAnswer($teamRequest);

                $this->addFlash('success', 'The user has been successfully accepted !');
            } else {
                $this->addFlash('warning', 'Already aswered: '.$answer.' !');
            }
        } else {
            $this->addFlash('warning', 'The token is not valid !');
        }

        return $this->redirectToRoute('team_request_index');
    }

    /**
     * @Route("/decline/{id}", name="team_request_decline")
     * @Security("user.isAdministratorOf(teamRequest.getTeam())")
     */
    public function declineAction(TeamRequest $teamRequest, Request $request)
    {
        if ($this->isCsrfTokenValid('decline-'.$teamRequest->getId(), $request->get('token'))) {
            if ('requested' === $answer = $teamRequest->getAnswer()) {
                $teamRequest->setAnswerDate(new \DateTime());
                $teamRequest->setAnswer('declined');

                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->get('AppBundle\Utils\Mailer')->sendTeamRequestAnswer($teamRequest);

                $this->addFlash('success', 'The user has been successfully declined !');
            } else {
                $this->addFlash('warning', 'Already aswered: '.$answer.' !');
            }
        } else {
            $this->addFlash('warning', 'The token is not valid !');
        }

        return $this->redirectToRoute('team_request_index');
    }

    public function numberRequestsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $numberRequests = $em->getRepository('AppBundle:TeamRequest')->countRequests($this->getUser());

        return $this->render('team_request/number_requests.html.twig', [
            'numberRequests' => $numberRequests,
        ]);
    }
}
