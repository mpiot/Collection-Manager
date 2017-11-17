<?php

namespace AppBundle\Utils;

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
}
