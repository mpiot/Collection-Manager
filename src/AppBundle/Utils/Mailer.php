<?php

// src/AppBundle/Utils/Mailer.php

namespace AppBundle\Utils;

use AppBundle\Entity\Project;
use AppBundle\Entity\TeamRequest;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Service Mailer, permettant d'envoyer les mails.
 */
class Mailer
{
    protected $mailer;
    protected $templating;
    private $senderMail;
    private $senderName;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $senderMail, $senderName)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->senderMail = $senderMail;
        $this->senderName = $senderName;
    }

    /**
     * Fonction principale d'envois de mail, défini les attributs de SwiftMailer.
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    protected function sendEmailMessage($to, $subject, $body)
    {
        $message = \Swift_Message::newInstance();
        $message
            ->setFrom($this->senderMail, $this->senderName)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setCharset('utf-8')
            ->setContentType('text/html');

        $this->mailer->send($message);
    }

    /**
     * Send an email to confirm a user registration.
     *
     * @param User $user
     */
    public function sendUserConfirmation(User $user)
    {
        $to = $user->getEmail();
        $subject = 'Registration confirmation';
        $body = $this->templating->render('mail/userConfirmation.html.twig', [
            'user' => $user,
        ]);

        $this->sendEmailMessage($to, $subject, $body);
    }

    /**
     * Send an email to reset the password.
     *
     * @param User $user
     */
    public function sendPasswordResetting(User $user)
    {
        $to = $user->getEmail();
        $subject = 'Password resetting';
        $body = $this->templating->render('mail/passwordResetting.html.twig', [
            'user' => $user,
        ]);

        $this->sendEmailMessage($to, $subject, $body);
    }

    /**
     * Send an email to confirm a request as been sent.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestConfirmation(TeamRequest $teamRequest)
    {
        $to = $teamRequest->getUser()->getEmail();
        $subject = 'Confirmation of your team request';
        $body = $this->templating->render('mail/confirmationTeamRequest.html.twig', ['teamRequest' => $teamRequest]);

        $this->sendEmailMessage($to, $subject, $body);
    }

    /**
     * Send an email to inform admin about a request.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestNotification(TeamRequest $teamRequest)
    {
        $subject = 'Team request notification';

        foreach ($teamRequest->getTeam()->getAdministrators() as $teamAdmin) {
            $body = $this->templating->render('mail/teamRequestNotification.html.twig', [
                'teamRequest' => $teamRequest,
                'teamAdmin' => $teamAdmin,
            ]);
            $this->sendEmailMessage($teamAdmin->getEmail(), $subject, $body);
        }
    }

    /**
     * Send an email to inform user about the answer.
     *
     * @param TeamRequest $teamRequest
     */
    public function sendTeamRequestAnswer(TeamRequest $teamRequest)
    {
        $to = $teamRequest->getUser()->getEmail();
        $subject = 'Team request answer';
        $body = $this->templating->render('mail/teamRequestAnswer.html.twig', ['teamRequest' => $teamRequest]);

        $this->sendEmailMessage($to, $subject, $body);
    }

    /**
     * Send an email to inform user about his project admin role.
     *
     * @param Project $project
     */
    public function sendProjectAdminNotification(Project $project)
    {
        $to = '';
        $subject = 'Project administration notification';
        $body = $this->templating->render('mail/projectAdminNotification.html.twig', [
            'project' => $project,
        ]);

        $this->sendEmailMessage($to, $subject, $body);
    }
}
