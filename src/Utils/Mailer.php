<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
