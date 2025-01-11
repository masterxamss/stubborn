<?php

namespace App\Service;

use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        readonly private EntityManagerInterface $entityManagerInterface
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendActivationEmail(string $toEmail, string $activationToken): void
    {
        $activationLink = sprintf('http://127.0.0.1:8000/activate/%s', $activationToken);
        $htmlContent = $this->twig->render('emails/activation.html.twig', [
            'activationLink' => $activationLink,
        ]);

        $this->email($toEmail, 'Stubborn - Activer votre compte', $htmlContent);
    }

    public function sendLowStockEmail($products): void
    {
        $getAdminUsers = $this->entityManagerInterface->getRepository(User::class)->findAdmins();

        $htmlContent = $this->twig->render('emails/low_stock.html.twig', [
            'products' => $products,
        ]);

        foreach ($getAdminUsers as $user) {
            $this->email($user->getEmail(), 'Stubborn - Stock bas', $htmlContent);
        }
    }

    private function email($toEmail, $subject, $htmlContent): void
    {
        $email = (new Email())
            ->from('no-reply@exemplo.com')
            ->to($toEmail)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
