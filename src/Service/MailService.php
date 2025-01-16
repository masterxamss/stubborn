<?php

namespace App\Service;

use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * MailService is responsible for handling email-related operations within the application.
 * It provides methods for sending account activation emails and notifications about low stock.
 */
class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    /**
     * Constructor for the MailService.
     *
     * @param MailerInterface $mailer The mailer interface for sending emails.
     * @param Environment $twig The Twig environment for rendering email templates.
     * @param EntityManagerInterface $entityManagerInterface The entity manager for database operations.
     */
    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        readonly private EntityManagerInterface $entityManagerInterface
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Sends an activation email to the specified recipient.
     *
     * @param string $toEmail The recipient's email address.
     * @param string $activationToken The activation token to be included in the email.
     * @return void
     */
    public function sendActivationEmail(string $toEmail, string $activationToken): void
    {
        $activationLink = sprintf('http://127.0.0.1:8000/activate/%s', $activationToken);
        $htmlContent = $this->twig->render('emails/activation.html.twig', [
            'activationLink' => $activationLink,
        ]);

        $this->email($toEmail, 'Stubborn - Activer votre compte', $htmlContent);
    }

    /**
     * Sends a low stock notification email to all administrators.
     *
     * @param array $products A list of products with low stock levels.
     * @return void
     */
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

    /**
     * Sends an email to the specified recipient.
     *
     * @param string $toEmail The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $htmlContent The HTML content of the email.
     * @return void
     */
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
