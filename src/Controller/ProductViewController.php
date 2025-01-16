<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * ProductViewController handles product-related views, including single product details and product listings.
 */
class ProductViewController extends AbstractController
{
    /**
     * Displays the details of a specific product based on its ID.
     *
     * @param EntityManagerInterface $entityManager Provides access to the database for retrieving the product.
     * @param int $id The ID of the product to retrieve.
     * @return Response Renders the product details template.
     *
     * @Route("/product/{id}", name="app_product_view", methods={"GET"})
     */
    #[Route('/product/{id}', name: 'app_product_view', methods: ['GET'])]
    public function getProduct(EntityManagerInterface $entityManager, int $id): Response
    {
        // Retrieve the product from the database
        $getProduct = $entityManager->getRepository(Products::class)->find($id);
        // Render the product view template with the product data
        return $this->render('product_view/productView.html.twig', [
            'product' => $getProduct,
            'path' => 'products'
        ]);
    }

    /**
     * Displays a list of products filtered by an optional price range.
     *
     * @param Request $request Provides access to the HTTP request, including query parameters.
     * @param ProductsRepository $productsRepository Provides custom repository methods for querying products.
     * @return Response Renders the product list template.
     *
     * @Route("/products", name="app_products")
     */
    #[Route('/products', name: 'app_products')]
    public function getProducts(Request $request, ProductsRepository $productsRepository): Response
    {
        // Retrieve the price range from the query parameters
        $priceRange = $request->query->get('price_range');
        // Retrieve the products based on the price range
        $products = $productsRepository->findByPriceRange($priceRange);
        // Render the products list template with the filtered data
        return $this->render('product_view/products.html.twig', [
            'products' => $products,
            'path' => 'products'
        ]);
    }
}
