<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Cart;
use App\Entity\Products;
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
        $getUser = $this->getUser();
        $cartItems = $this->entityManagerInterface->getRepository(Cart::class)->findBy(['user' => $getUser]);

        $data = [];
        $total = 0;

        $order = new Order();
        $this->entityManagerInterface->persist($order);

        if ($cartItems) {
            foreach ($cartItems as $cart) {
                $data[] = [
                    'product' => $cart->getProduct()->getName(),
                    'price' => $cart->getProduct()->getPrice(),
                    'quantity' => $cart->getQuantity(),
                ];
                $total += $cart->getProduct()->getPrice() * $cart->getQuantity();
            }

            $order->setUser($getUser);
            $order->setTotalPrice($total);
            $order->setStatus(false);
            $order->setCreatedAt(new \DateTimeImmutable());

            $this->entityManagerInterface->flush();

            $url = $this->stripeServiceInterface->createPayment($data, $order);
            return $this->redirect($url, Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('info', 'Panier vide.');
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/order/success/{order}', name: 'app_stripe_success')]
    public function success(Order $order): Response
    {
        // update order status
        $order->setStatus(true);
        $order->setPaymentId($this->stripeServiceInterface->getPaymentId());

        $this->entityManagerInterface->flush();

        // clean cart
        $getUser = $this->getUser();
        $cartItems = $this->entityManagerInterface->getRepository(Cart::class)->findBy(['user' => $getUser]);

        if ($cartItems) {
            foreach ($cartItems as $cart) {

                $size = $cart->getSize();
                $stock = $cart->getProduct()->getStock();
                $stock[$size] = $stock[$size] - $cart->getQuantity();
                $cart->getProduct()->setStock($stock);

                $this->entityManagerInterface->remove($cart);
                $this->entityManagerInterface->flush();
            }
        }

        // send email if stock < 10
        $getLowStockProducts = $this->productsRepository->findLowStockProducts();

        if ($getLowStockProducts) {
            $this->mailService->sendLowStockEmail($getLowStockProducts);
        }


        return $this->render('stripe/success.html.twig', [
            'order' => $order,
            'path' => '',
        ]);
    }

    #[Route('/stripe/cancel/{order}', name: 'app_stripe_cancel')]
    public function cancel(Order $order): Response
    {

        return $this->render('stripe/cancel.html.twig', []);
    }
}
