<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * LoginController handles user authentication by rendering the login page and managing login errors.
 */
class LoginController extends AbstractController
{
    /**
     * Renders the login page and handles authentication errors.
     *
     * @param AuthenticationUtils $authenticationUtils Provides authentication error details and the last username attempted.
     * @return Response Renders the login template with necessary authentication data.
     *
     * @Route("/login", name="app_login")
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Retrieve the last authentication error, if any
        $error = $authenticationUtils->getLastAuthenticationError();
        // Retrieve the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        // Render the login page with authentication data
        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'path' => 'login'
        ]);
    }
}
