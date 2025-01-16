<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ActivationController handles user account activation via an activation token.
 */
class ActivationController extends AbstractController
{
    /**
     * Activates a user account based on the provided activation token.
     *
     * @param string $token The activation token.
     * @param UserRepository $userRepository The repository to fetch user data.
     * @param EntityManagerInterface $entityManager Manages database updates.
     * @return Response Redirects the user to the login page with success or error messages.
     *
     * @Route("/activate/{token}", name="app_activate")
     */
    #[Route('/activate/{token}', name: 'app_activate')]
    public function activate(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        try {

            $user = $userRepository->findOneBy(['activationToken' => $token]);

            if (!$user) {
                $this->addFlash('error', 'Token d\'activation invalide ou expiré.');
                return $this->redirectToRoute('app_login');
            }

            $user->setIsVerified(true);
            $user->setActivationToken(null);
            $entityManager->flush();

            $this->addFlash('success', 'Félicitations, votre compte a été activée avec succès ! Vous pouvez maintenant vous connecter. 🎉🎉🎉');
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de l\'activation de votre compte.');
            return $this->redirectToRoute('app_login');
        }
    }
}
