<?php

namespace App\Utils;

use App\Entity\User;

/**
 * Service Mailer, permettant d'envoyer les mails.
 */
class Mailer
{
    protected $mailer;
    protected $templating;
    private $senderMail;
    private $senderName;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, $senderMail, $senderName)
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
        $message = (new \Swift_Message())
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

        try {
            $body = $this->templating->render('mail/userConfirmation.html.twig', [
                'user' => $user,
            ]);
        } catch (\Exception $exception) {
            return new \Error('Template generation failed');
        }

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

        try {
            $body = $this->templating->render('mail/passwordResetting.html.twig', [
                'user' => $user,
            ]);
        } catch (\Exception $exception) {
            return new \Error('Template generation failed');
        }

        $this->sendEmailMessage($to, $subject, $body);
    }
}
