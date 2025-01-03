<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductViewController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_product_view')]
    public function getProduct(EntityManagerInterface $entityManager, int $id): Response
    {
        $getProduct = $entityManager->getRepository(Products::class)->find($id);
        
        return $this->render('product_view/productView.html.twig', [
            'product' => $getProduct
        ]);
    }

    #[Route('/products', name: 'app_products')]
    public function getProducts(Request $request, ProductsRepository $productsRepository): Response
    {
        $priceRange = $request->query->get('price_range');

        $products = $productsRepository->findByPriceRange($priceRange);

        return $this->render('product_view/products.html.twig', [
            'products' => $products
        ]);
    }
}
