<?php

namespace App\Controller;

use App\Entity\Products;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HomeController manages the display of the homepage and highlighted products.
 */
class HomeController extends AbstractController
{
    /**
     * Displays the homepage with a list of highlighted products.
     *
     * @param EntityManagerInterface $entityManager Manages the database operations.
     * @return Response Renders the homepage with highlighted products.
     *
     * @Route("/", name="app_index")
     */
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch highlighted products from the database
        $getProductsHighLight = $entityManager->getRepository(Products::class)->findBy(['highLighted' => true]);

        // Render the homepage template with the fetched products
        return $this->render('home/home.html.twig', [
            'products' => $getProductsHighLight,
            'path' => 'home'
        ]);
    }
}
