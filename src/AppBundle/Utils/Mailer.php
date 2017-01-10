<?php

// src/AppBundle/Utils/Mailer.php

namespace AppBundle\Utils;

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

    public function __construct($mailer, EngineInterface $templating, $mailer_from, $mailer_name)
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
    protected function sendEmailMessage($to, $from, $subject, $body)
    {
        $message = \Swift_Message::newInstance();
        $message
            ->setFrom($from)
            ->setTo($to)
            ->setFrom($from)
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
        $to = $teamRequest->getUser()->getEmail();
        $from = [$this->from => $this->name];
        $subject = 'Confirmation of your team request';
        $body = $this->templating->render('mail/confirmationTeamRequest.html.twig', array('teamRequest' => $teamRequest));

        $this->sendEmailMessage($to, $from, $subject, $body);
    }

    /**
     * Send an email to inform admin about a request.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestNotification(TeamRequest $teamRequest)
    {
        $teamAdministratorsEmail = [];
        foreach ($teamRequest->getTeam()->getAdministrators() as $teamAdministrator) {
            $teamAdministratorsEmail[] = $teamAdministrator->getEmail();
        }

        $to = $teamAdministratorsEmail;
        $from = [$this->from => $this->name];
        $subject = 'Team request notification';
        $body = $this->templating->render('mail/teamRequestNotification.html.twig', array('teamRequest' => $teamRequest));

        $this->sendEmailMessage($to, $from, $subject, $body);
    }

    /**
     * Send an email to inform user about the answer.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestAnswer(TeamRequest $teamRequest)
    {
        $to = $teamRequest->getUser()->getEmail();
        $from = [$this->from => $this->name];
        $subject = 'Team request answer';
        $body = $this->templating->render('mail/teamRequestAnswer.html.twig', array('teamRequest' => $teamRequest));

        $this->sendEmailMessage($to, $from, $subject, $body);
    }
}
