<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Cart;
use App\Entity\Products;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Service\StripeServiceInterface;
use App\Service\MailService;
use App\Repository\ProductsRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
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

    #[Route('/stripe', name: 'app_stripe')]
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

    #[Route('/order/success/{order}', name: 'app_stripe_success')]
    public function success(Order $order): Response
    {
        try {
            // update order status
            $order->setStatus(true);
            $order->setPaymentId($this->stripeServiceInterface->getPaymentId());
            $this->entityManagerInterface->persist($order);
            //$this->entityManagerInterface->flush();

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

            return $this->render('stripe/success.html.twig', [
                'order' => $order,
                'user' => $this->getUser(),
                'path' => '',
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/stripe/cancel/{order}', name: 'app_stripe_cancel')]
    public function cancel(Order $order): Response
    {

        return $this->render('stripe/cancel.html.twig', []);
    }
}
