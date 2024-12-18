<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailService $mailService): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('error', 'Impossible de créer l\'utilisateur');
                return $this->render('register/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $activationToken = Uuid::v4();
            $user->setActivationToken($activationToken);
            
            $entityManager->persist($user);
            $entityManager->flush();

            $mailService->sendActivationEmail($user->getEmail(), $activationToken);

            $this->addFlash('success', 'Enregistrement réussi. Vérifiez votre e-mail pour activer votre compte');
        }

        return $this->render('register/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

