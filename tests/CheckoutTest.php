<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\Products;
use App\Entity\User;
use App\Service\StripeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CheckoutTest
 * 
 * This test suite validates the checkout flow, including:
 * 
 * 1. Creating a test user and logging them in.
 * 2. Creating a test product and adding it to the user's cart.
 * 3. Configuring a mock Stripe service to simulate payment processing.
 * 4. Sending a request to the checkout controller.
 * 5. Verifying the redirection to Stripe's checkout URL.
 * 6. Validating the creation of an order in the database.
 */
class CheckoutTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     * Client for simulating HTTP requests.
     */
    private $client;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface $entityManager
     * Entity manager for database operations.
     */
    private $entityManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StripeServiceInterface $stripeServiceMock
     * Mock object for the StripeService.
     */
    private $stripeServiceMock;

    /**
     * Set up the test environment.
     * Initializes the client, entity manager, and StripeService mock.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // Create mock for StripeService
        $this->stripeServiceMock = $this->createMock(StripeServiceInterface::class);
        $this->client->getContainer()->set(StripeServiceInterface::class, $this->stripeServiceMock);
    }

    /**
     * Tear down the test environment.
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->close();
        }
        $this->entityManager = null;
    }

    /**
     * Test the checkout flow with a mock Stripe service.
     * Verifies user creation, product addition, and order generation.
     * 
     * @return void
     */
    public function testCheckoutFlowWithStripe(): void
    {
        // 1. Create test user and login
        $user = $this->createTestUser();
        $this->client->loginUser($user);

        // 2. Create test product and add to cart
        $product = $this->createTestProduct();
        $this->addToCart($product, $user);

        // Configure mock for StripeService
        $this->stripeServiceMock->method('createPayment')
            ->willReturn('https://checkout.stripe.com/test-session-url');

        // 3. Make a request to controller
        $this->client->request('GET', '/checkout');

        // 4. Verify redirect to Stripe
        $this->assertResponseStatusCodeSame(303); // HTTP_SEE_OTHER
        $this->assertEquals(
            'https://checkout.stripe.com/test-session-url',
            $this->client->getResponse()->headers->get('Location')
        );

        // 5. Verify order creation
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame(100.00, $order->getTotalPrice());
    }

    /**
     * Create a test user.
     * 
     * @return User The created user entity.
     */
    private function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPassword(password_hash('adminpass', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $user->setName('Admin User');
        $user->setDeliveryAddress('Admin Address');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create a test product.
     * 
     * @return Products The created product entity.
     */
    private function createTestProduct(): Products
    {
        $product = new Products();
        $product->setName('Test Product');
        $product->setPrice(50.00);
        $product->setStock([
            'XS' => 10,
            'S' => 20,
            'M' => 30,
            'L' => 40,
            'XL' => 50,
        ]);
        $product->setHighLighted(true);
        $product->setImage('image_test.jpg');

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Add a product to the cart for a specific user.
     * 
     * @param Products $product The product
     */
    private function addToCart(Products $product, User $user): void
    {
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setProduct($product);
        $cart->setQuantity(2);
        $cart->setSize('XS');

        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }
}
