<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Cart;
use App\Entity\Products;
use App\Entity\User;

/**
 * Test class for adding items to the shopping cart.
 * 
 * This test verifies the entire flow of adding a product to the cart by simulating a user's actions.
 * It ensures that a user can successfully add a product to the cart and that the cart is updated in the database.
 * 
 * The test covers the following steps:
 * 1. Creating a test user and a test product.
 * 2. Logging in the user.
 * 3. Retrieving the CSRF token for the form.
 * 4. Sending a POST request to add the product to the cart.
 * 5. Verifying that the response redirects to the cart page.
 * 6. Following the redirect and checking that the response is successful.
 * 7. Verifying that the cart item has been saved in the database with correct details.
 * 
 */
class AddToCartTest extends WebTestCase
{
    private $entityManager;
    private $client;

    // Test setup and teardown methods
    protected function setUp(): void
    {
        // Iniciate the client to make requests
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->close();
        }
        $this->entityManager = null; // Prevent memory leaks

    }

    /**
     * Creates a test user for simulating login and cart operations.
     *
     * @return User The created User entity.
     */
    private function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setName('Test User');
        $user->setDeliveryAddress('123 Main Street, City, Country');
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Creates a test product to be added to the cart.
     *
     * @return Products The created Product entity.
     */
    private function createTestProduct(): Products
    {
        $product = new Products();
        $product->setName('Test Product');
        $product->setPrice(19.99);
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
     * Tests the process of adding an item to the cart.
     * 
     * This test verifies that a user can successfully add a product to their cart
     * and that the item is correctly persisted in the database.
     * 
     * @return void
     */
    public function testAddToCart(): void
    {
        // Step 1: Create a test user and product
        $user = $this->createTestUser();
        $product = $this->createTestProduct();

        $this->client->loginUser($user);

        // Step 2: Get the CSRF token
        $crawler = $this->client->request('GET', '/product/' . $product->getId());

        $csrfToken = $crawler->filter('form input[name="token"]')->attr('value');

        // Step 3: Send a POST request to add the item to the cart
        $this->client->request('POST', '/cart/add-item', [
            'product_id' => $product->getId(),
            'size' => 'M',  // Exemplo de tamanho
            'user' => $user->getId(),
            'token' => $csrfToken,
        ]);

        // Step 4: Verify if the response was redirected to the cart
        $this->assertResponseRedirects('/cart');

        // Step 5: Follow the redirect
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Step 6: Verify if the item was persisted in the database
        $cartItem = $this->entityManager->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'product' => $product,
            'size' => 'M',
        ]);

        $this->assertNotNull($cartItem);
        $this->assertEquals(1, $cartItem->getQuantity());
        $this->assertEquals('M', $cartItem->getSize());
    }
}
