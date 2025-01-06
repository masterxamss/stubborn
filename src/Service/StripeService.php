<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;

class StripeService implements StripeServiceInterface
{
    private const STRIPE_PAYMENT_ID = 'session_payment_id';
    private const STRIPE_PAYMENT_ORDER = 'session_stripe_payment_order';

    public function __construct(
        readonly private string $stripeSecretKey,
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private RequestStack $requestStack,
        readonly private EntityManagerInterface $entityManagerInterface
    ) {
        Stripe::setApiKey($stripeSecretKey);
    }

    public function createPayment($cart, $orderId): string
    {

        $session = Session::create([
            'shipping_address_collection' => [
                'allowed_countries' => ['GB', 'FR']
            ],
            'customer_email' => $orderId->getUser()->getEmail(),
            'success_url' => $this->urlGenerator->generate(
                'app_stripe_success',
                ['order' => $orderId->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'cancel_url' => $this->urlGenerator->generate(
                'app_stripe_cancel',
                ['order' => $orderId->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'payment_method_types' => ['card'],
            'line_items' => $this->getLinesItems($cart),
            'mode' => 'payment',
        ]);

        $this->requestStack->getSession()->set(self::STRIPE_PAYMENT_ID, $session->id);
        $this->requestStack->getSession()->set(self::STRIPE_PAYMENT_ORDER, $orderId->getId());

        //$orderId->setPaymentId($this->getPaymentId());

        //$this->entityManagerInterface->flush();

        return $session->url;
    }

    public function getPaymentId(): ?string
    {
        return $this->requestStack->getSession()?->get(self::STRIPE_PAYMENT_ID);
    }

    public function getPaymentOrder(): ?string
    {
        return $this->requestStack->getSession()?->get(self::STRIPE_PAYMENT_ORDER);
    }

    private function getLinesItems($cart): array
    {
        $products = [];

        foreach ($cart as $item) {
            $products[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['product'],
                    ],
                    'unit_amount' => (int) ($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        return $products;
    }
}
