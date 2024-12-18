<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivationController extends AbstractController
{
    #[Route('/activate/{token}', name: 'app_activate')]
    public function activate(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
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
    }
}
