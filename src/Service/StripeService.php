<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Stripe\Stripe;
use Stripe\StripeClient;
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
        readonly private EntityManagerInterface $entityManagerInterface,
    ) {
        Stripe::setApiKey($stripeSecretKey);
    }

    public function createPayment($cart, $orderId): string
    {
        $stripe = new StripeClient($this->stripeSecretKey);

        $session = Session::create([
            'shipping_address_collection' => [
                'allowed_countries' => ['GB', 'FR'],
            ],
            'customer' => $stripe->customers->create([
                'name' => $orderId->getUser()->getName(),
                'email' => $orderId->getUser()->getEmail(),
                /*'shipping' => [
                    'name' => $orderId->getUser()->getName(),
                    'address' => [
                        'line1' => $orderId->getUser()->getElementAddress('street'),
                        'city' => $orderId->getUser()->getElementAddress('city'),
                        'state' => $orderId->getUser()->getElementAddress('state'),
                        'postal_code' => $orderId->getUser()->getElementAddress('zipCode'),
                        'country' => $orderId->getUser()->getElementAddress('country'),
                    ]
                ],*/
            ]),
            'payment_method_types' => ['card'],
            'line_items' => $this->getLinesItems($cart),
            'mode' => 'payment',
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
        ]);

        $this->requestStack->getSession()->set(self::STRIPE_PAYMENT_ID, $session->id);
        $this->requestStack->getSession()->set(self::STRIPE_PAYMENT_ORDER, $orderId->getId());

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
