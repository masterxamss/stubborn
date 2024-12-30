<?php

namespace App\Controller;

use App\Entity\Products;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $getProductsHighLight = $entityManager->getRepository(Products::class)->findBy(['highLighted' => true]);

        return $this->render('home/home.html.twig', [
            'products' => $getProductsHighLight
        ]);
    }
}
