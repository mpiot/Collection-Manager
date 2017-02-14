<?php

// src/AppBundle/Utils/Mailer.php

namespace AppBundle\Utils;

use AppBundle\Entity\Project;
use AppBundle\Entity\TeamRequest;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Service Mailer, permettant d'envoyer les mails.
 */
class Mailer
{
    protected $mailer;
    protected $templating;
    private $from;
    private $name;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $mailer_from, $mailer_name)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->from = $mailer_from;
        $this->name = $mailer_name;
    }

    /**
     * Fonction principale d'envois de mail, défini les attributs de SwiftMailer.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    protected function sendEmailMessage($from, $to, $subject, $body)
    {
        $message = \Swift_Message::newInstance();
        $message
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setCharset('utf-8')
            ->setContentType('text/html');

        $this->mailer->send($message);
    }

    /**
     * Send an email to confirm a request as been sent.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestConfirmation(TeamRequest $teamRequest)
    {
        $from = [$this->from => $this->name];
        $to = $teamRequest->getUser()->getEmail();
        $subject = 'Confirmation of your team request';
        $body = $this->templating->render('mail/confirmationTeamRequest.html.twig', array('teamRequest' => $teamRequest));

        $this->sendEmailMessage($from, $to, $subject, $body);
    }

    /**
     * Send an email to inform admin about a request.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestNotification(TeamRequest $teamRequest)
    {
        $from = [$this->from => $this->name];
        $subject = 'Team request notification';

        foreach ($teamRequest->getTeam()->getAdministrators() as $teamAdmin) {
            $body = $this->templating->render('mail/teamRequestNotification.html.twig', array(
                'teamRequest' => $teamRequest,
                'teamAdmin' => $teamAdmin,
            ));
            $this->sendEmailMessage($from, $teamAdmin->getEmail(), $subject, $body);
        }
    }

    /**
     * Send an email to inform user about the answer.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestAnswer(TeamRequest $teamRequest)
    {
        $from = [$this->from => $this->name];
        $to = $teamRequest->getUser()->getEmail();
        $subject = 'Team request answer';
        $body = $this->templating->render('mail/teamRequestAnswer.html.twig', array('teamRequest' => $teamRequest));

        $this->sendEmailMessage($from, $to, $subject, $body);
    }

    /**
     * Send an email to inform user about his project admin role.
     *
     * @param Project $project
     */
    public function sendProjectAdminNotification(Project $project, $changeset)
    {
        $from = [$this->from => $this->name];
        $to = '';
        $subject = 'Project administration notification';
        $body = $this->templating->render('mail/projectAdminNotification.html.twig', array(
            'project' => $project
        ));

        $this->sendEmailMessage($from, $to, $subject, $body);
    }
}
