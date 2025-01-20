<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Cart;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Service\StripeServiceInterface;
use App\Service\MailService;
use App\Repository\ProductsRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckOutController extends AbstractController
{
    public function __construct(
        private readonly StripeServiceInterface $stripeServiceInterface,
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly OrderRepository $orderRepository,
        private readonly MailService $mailService,
        private ProductsRepository $productsRepository
    ) {
        $this->productsRepository = $productsRepository;
    }

    /**
     * Route for handling the checkout process and payment via Stripe.
     * 
     * This function manages the user's checkout process by:
     * 1. Checking if the user's cart is empty. If it is, it redirects to the cart page with a flash message.
     * 2. Starting a database transaction to ensure the integrity of the order creation process.
     * 3. Creating an order object and associating it with the logged-in user.
     * 4. Iterating over the cart items, calculating the total price, and creating `OrderItem` objects for each cart item.
     * 5. Persisting the order and order items in the database.
     * 6. Creating a Stripe payment link using the `StripeServiceInterface`.
     * 7. Committing the transaction to finalize the order creation process.
     * 8. If an error occurs during any step, it rolls back the transaction and shows an error message.
     * 
     * @param void
     * @return Response Redirects to Stripe payment URL or the cart page with an error message.
     */
    #[Route('/checkout', name: 'app_stripe', methods: ['GET', 'POST'])]
    public function checkout(): Response
    {
        $user = $this->getUser();
        $cartItems = $this->entityManagerInterface->getRepository(Cart::class)->findBy(['user' => $user]);

        if (!$cartItems) {
            $this->addFlash('info', 'Panier vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Begin Transaction
        $this->entityManagerInterface->getConnection()->beginTransaction();

        try {
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(false);
            $order->setCreatedAt(new \DateTimeImmutable());
            $this->entityManagerInterface->persist($order);

            $data = [];
            $total = 0;

            foreach ($cartItems as $cart) {
                $product = $cart->getProduct();

                // Calculate total and prepare data for Stripe
                $quantity = $cart->getQuantity();
                $price = $product->getPrice();
                $total += $price * $quantity;

                $data[] = [
                    'product' => $product->getName(),
                    'price' => $price,
                    'quantity' => $quantity,
                ];

                // Create OrderItem
                $orderItem = new OrderItem();
                $orderItem->setOrders($order);
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($price);
                $orderItem->setSize($cart->getSize());
                $this->entityManagerInterface->persist($orderItem);
            }

            // Update total price of the order
            $order->setTotalPrice($total);

            $this->entityManagerInterface->flush();

            // Create stripe payment
            $url = $this->stripeServiceInterface->createPayment($data, $order);

            // Confirm transaction
            $this->entityManagerInterface->getConnection()->commit();

            return $this->redirect($url, Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            // Revert transaction in case of error
            //$this->addFlash('error', $e->getMessage());
            $this->entityManagerInterface->getConnection()->rollBack();
            $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.');

            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/checkout/success/{order}', name: 'app_stripe_success')]
    public function success(Order $order): Response
    {
        try {
            // update order status
            $order->setStatus(true);
            $order->setPaymentId($this->stripeServiceInterface->getPaymentId());
            $this->entityManagerInterface->persist($order);

            // clean cart
            $getUser = $this->getUser();
            $cartItems = $this->entityManagerInterface->getRepository(Cart::class)->findBy(['user' => $getUser]);

            if ($cartItems) {
                foreach ($cartItems as $cart) {
                    // update stock
                    $size = $cart->getSize();
                    $stock = $cart->getProduct()->getStock();
                    $stock[$size] = $stock[$size] - $cart->getQuantity();
                    $cart->getProduct()->setStock($stock);

                    $this->entityManagerInterface->remove($cart);
                }
            }

            $this->entityManagerInterface->flush();

            // send email if stock < 10
            $getLowStockProducts = $this->productsRepository->findLowStockProducts();
            if ($getLowStockProducts) {
                $this->mailService->sendLowStockEmail($getLowStockProducts);
            }

            $getOrderItems = $this->entityManagerInterface->getRepository(OrderItem::class)->findBy(['orders' => $order->getId()]);

            return $this->render('checkout/success.html.twig', [
                'order' => $order,
                'user' => $getUser,
                'orderItems' => $getOrderItems,
                'path' => '',
            ]);
        } catch (\Exception $e) {
            //$this->addFlash('error', $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.');
            return $this->render('checkout/success.html.twig', [
                'order' => [],
                'user' => [],
                'orderItems' => [],
                'path' => '',
            ]);
        }
    }

    #[Route('/checkout/cancel/{order}', name: 'app_stripe_cancel')]
    public function cancel(Order $order): Response
    {
        $orderItem = $this->entityManagerInterface->getRepository(OrderItem::class)->findBy(['orders' => $order->getId()]);
        foreach ($orderItem as $item) {
            $this->entityManagerInterface->remove($item);
        }

        $this->entityManagerInterface->remove($order);
        $this->entityManagerInterface->flush();

        return $this->redirectToRoute('app_cart');
    }
}
