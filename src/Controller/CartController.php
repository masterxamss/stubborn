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

class CartController extends AbstractController
{
    /**
     * Display the cart page.
     *
     * This function will display the cart page and calculate the total price of the items in the cart.
     *
     * @param EntityManagerInterface $entityManager The entity manager.
     *
     * @return Response The response to send back.
     */
    #[Route('/cart', name: 'app_cart')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        try {
            $getUser = $this->getUser();

            // Check if the user is logged in
            if (!$getUser) {
                $this->addFlash('error', 'Vous devez être connecté pour accéder à votre panier.');
                return $this->redirectToRoute('app_login');
            }

            $cartItems = $entityManager->getRepository(Cart::class)->findBy(['user' => $getUser]);

            $total = 0;

            // Calculate the total price
            if ($cartItems) {
                foreach ($cartItems as $cart) {
                    $productPrice = $cart->getProduct()->getPrice();
                    $quantity = $cart->getQuantity();
                    $total += $productPrice * $quantity;
                }
            }

            return $this->render('cart/cart.html.twig', [
                'carts' => $cartItems,
                'total' => $total,
                'path' => 'cart'
            ]);
        } catch (\Exception $e) {
            //$this->addFlash('error', $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération du panier.');
            return $this->redirectToRoute('app_products');
        }
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
        try {
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

            return $this->redirectToRoute('app_cart');
        } catch (\Exception $e) {
            //$this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_products');
            $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout au panier.');
        }
    }

    /**
     * Deletes a cart item, given its id.
     * 
     * This function is called by a POST request and validates the CSRF token.
     * If the token is invalid, it redirects back with an error message.
     * If the cart item exists, it removes it and flushes the changes.
     * 
     * @param string $id The id of the cart item to delete.
     * @param Request $request The request that triggered this action.
     * @param EntityManagerInterface $entityManager The entity manager.
     * 
     * @return Response The response to send back, which will be a redirect.
     */
    #[Route('/cart/{id}/delete', name: 'app_cart_item_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Validate the CSRF token
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete_cart_item', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_products');
        }

        try {
            // Delete the cart item
            $cart = $entityManager->getRepository(Cart::class)->find($id);
            if ($cart) {
                $entityManager->remove($cart);
                $entityManager->flush();
            } else {
                $this->addFlash('error', 'Item introuvable.');
            }
        } catch (\Exception $e) {
            //$this->addFlash('error', $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
        }

        return $this->redirectToRoute('app_cart');
    }
}
