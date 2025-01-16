<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;

/**
 * StripeService is responsible for managing payments using Stripe.
 * It provides methods to create payment sessions and retrieve session data from the request stack.
 */
class StripeService implements StripeServiceInterface
{
    private const STRIPE_PAYMENT_ID = 'session_payment_id';
    private const STRIPE_PAYMENT_ORDER = 'session_stripe_payment_order';


    /**
     * Constructor for the StripeService.
     *
     * @param string $stripeSecretKey The secret API key for Stripe.
     * @param UrlGeneratorInterface $urlGenerator Generates URLs for success and cancel payment redirection.
     * @param RequestStack $requestStack Manages session data for Stripe payment sessions.
     * @param EntityManagerInterface $entityManagerInterface Manages database interactions.
     */
    public function __construct(
        readonly private string $stripeSecretKey,
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private RequestStack $requestStack,
        readonly private EntityManagerInterface $entityManagerInterface,
    ) {
        Stripe::setApiKey($stripeSecretKey);
    }

    /**
     * Creates a Stripe payment session and returns its URL.
     *
     * @param array $cart The cart items with details about products and quantities.
     * @param object $orderId The order object containing user and order details.
     * @return string The URL of the Stripe payment session.
     */
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

    /**
     * Retrieves the Stripe payment session ID from the session.
     *
     * @return string|null The Stripe payment session ID, or null if not found.
     */
    public function getPaymentId(): ?string
    {
        return $this->requestStack->getSession()?->get(self::STRIPE_PAYMENT_ID);
    }

    /**
     * Retrieves the order ID associated with the Stripe payment session.
     *
     * @return string|null The order ID, or null if not found.
     */
    public function getPaymentOrder(): ?string
    {
        return $this->requestStack->getSession()?->get(self::STRIPE_PAYMENT_ORDER);
    }

    /**
     * Prepares line items for the Stripe payment session based on the cart contents.
     *
     * @param array $cart The cart items with details about products and quantities.
     * @return array The formatted line items for the Stripe payment session.
     */
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
