<?php

namespace App\Controller;

use App\Entity\Products;

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
    public function getProducts(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priceRange = $request->query->get('price_range');

        if ($priceRange) {

            $repository = $entityManager->getRepository(Products::class);
            $queryBuilder = $repository->createQueryBuilder('p');

            switch($priceRange){
                case '10-29':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 10)
                                 ->setParameter('max', 29);                    
                    break;
                case '30-35':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 30)
                                 ->setParameter('max', 35);                    
                    break;
                case '35-50':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 35)
                                 ->setParameter('max', 50);                    
                    break;
            }

            $getAllProducts = $queryBuilder->getQuery()->getResult();

        } else {
            
            $getAllProducts = $entityManager->getRepository(Products::class)->findAll();
        }

        return $this->render('product_view/products.html.twig', [
            'products' => $getAllProducts
        ]);
    }
}
