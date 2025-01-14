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
    /**
     * Route for user registration.
     * 
     * This function handles the registration of a new user by validating the form,
     * checking if the email is already in use, and if the form is valid, 
     * creating the user in the database and sending an activation email.
     * 
     * Steps:
     * 1. Create a new `User` object for the form.
     * 2. Handle the request to populate the form.
     * 3. Check if a user with the same email already exists.
     * 4. If the email is already taken, show an error message.
     * 5. If the form is valid:
     *    - The user's password is hashed.
     *    - A unique activation token is generated.
     *    - The user is saved to the database.
     *    - An activation email is sent to the user.
     * 
     * @param Request $request The HTTP request containing the form data.
     * @param EntityManagerInterface $entityManager Interface to interact with the database.
     * @param UserPasswordHasherInterface $passwordHasher Interface for hashing the user password.
     * @param MailService $mailService Service for sending the activation email.
     * @return Response The response rendering the registration form.
     */

    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailService $mailService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Check if the user already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            // If the user already exists, display an error message
            if ($existingUser) {
                $this->addFlash('error', 'Impossible de créer l\'utilisateur');
                return $this->render('register/register.html.twig', [
                    'form' => $form->createView(),
                    'path' => 'register'
                ]);
            }

            // Hash the password
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            // Generate and set the activation token
            $activationToken = Uuid::v4();
            $user->setActivationToken($activationToken);

            try {
                // Save the user
                $entityManager->persist($user);
                $entityManager->flush();

                // Send the activation email
                $mailService->sendActivationEmail($user->getEmail(), $activationToken);

                $this->addFlash('success', 'Enregistrement réussi. Vérifiez votre e-mail pour activer votre compte 🎉');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur');
            }
        }

        return $this->render('register/register.html.twig', [
            'form' => $form->createView(),
            'path' => 'register'
        ]);
    }
}
