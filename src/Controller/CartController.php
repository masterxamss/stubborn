<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Products;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $getUser = $this->getUser();
        
        $cartItems = $entityManager->getRepository(Cart::class)->findBy(['user' => $getUser]);

        $total = 0;

        if ($cartItems) {
            foreach ($cartItems as $cart) {
                // Obter o preço do produto e multiplicar pela quantidade
                $productPrice = $cart->getProduct()->getPrice();
                $quantity = $cart->getQuantity();
                $total += $productPrice * $quantity; // Somar ao total geral
            }
        } else {
            $this->addFlash('info', 'Panier vide.');
        }

        return $this->render('cart/cart.html.twig', [
            'carts' => $cartItems,
            'total' => $total
        ]);
    }


    /**
     * Add a product to the cart.
     *
     * This function validates the CSRF token, searches for the product, validates the size,
     * searches for the user, creates the cart item, persists the cart item, and displays a success message.
     * If any of the steps fail, it will redirect to the page where the user can try again.
     *
     * @param Request $request The request that triggered this action.
     * @param EntityManagerInterface $entityManager The entity manager.
     * @return Response The response to send back, which will be a redirect if the form was valid.
     */
    #[Route('/cart/add-item', name: 'app_add_to_cart', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Validate the CSRF token
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('create_cart_item', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_products');
        }
    
        // Search for the product
        $productId = $request->request->get('product_id');
        $product = $entityManager->getRepository(Products::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_products');
        }
    
        // Validate size
        $size = $request->request->get('size');
        if (!$size) {
            $this->addFlash('error', 'Le champ "taille" est obligatoire.');
            return $this->redirectToRoute('app_product_view', ['id' => $productId]);
        }
    
        // Search for the user
        $userId = $request->request->get('user');
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_products');
        }
    
        // Create the cart item
        $cart = new Cart();
        $cart->setProduct($product);
        $cart->setQuantity(1);
        $cart->setSize($size);
        $cart->setUser($user);
    
        // Persist the cart item
        $entityManager->persist($cart);
        $entityManager->flush();
    
        // Success message
        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('app_product_view', ['id' => $productId]);
    }
}
