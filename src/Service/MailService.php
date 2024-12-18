<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendActivationEmail(string $toEmail, string $activationToken): void
    {
        $activationLink = sprintf('http://127.0.0.1:8000/activate/%s', $activationToken);
        $htmlContent = $this->twig->render('emails/activation.html.twig', [
            'activationLink' => $activationLink,
        ]);

        $email = (new Email())
            ->from('no-reply@exemplo.com')
            ->to($toEmail)
            ->subject('Activer votre compte')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
