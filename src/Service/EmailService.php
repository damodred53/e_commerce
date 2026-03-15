<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $senderEmail = 'noreply@ecommerce.local'
    ) {
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($user->getEmail())
            ->subject('Bienvenue sur notre plateforme !')
            ->html($this->getWelcomeEmailTemplate($user));

        $this->mailer->send($email);
    }

    private function getWelcomeEmailTemplate(User $user): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Bienvenue !</h1>
                    </div>
                    <div class="content">
                        <p>Bonjour <strong>{$user->getEmail()}</strong>,</p>
                        <p>Votre compte a été créé avec succès sur notre plateforme.</p>
                        <p>Vous pouvez désormais vous connecter avec vos identifiants et commencer à parcourir notre catalogue.</p>
                        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    </div>
                    <div class="footer">
                        <p>&copy; 2026 E-commerce. Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }
}