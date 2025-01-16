<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * AppFixtures is responsible for loading sample data into the database for development and testing.
 * 
 * This fixture loads users and products from JSON files, hashes user passwords, and persists the entities to the database.
 */
class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface The password hasher service for hashing user passwords.
     */
    private $passwordHasher;

    /**
     * AppFixtures constructor.
     * 
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service to hash user passwords.
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Loads sample data into the database.
     * 
     * This method reads user and product data from JSON files and creates `User` and `Products` entities.
     * It hashes user passwords before persisting and ensures that all data is saved in the database.
     * 
     * @param ObjectManager $manager The Doctrine ObjectManager to manage entity persistence.
     */
    public function load(ObjectManager $manager): void
    {
        // Define the paths for the user and product JSON data files
        $projectRoot = dirname(__DIR__, 2);
        $usersFile = $projectRoot . '/src/DataFixtures/data/users.json';
        $productsFile = $projectRoot . '/src/DataFixtures/data/products.json';

        // Decode the JSON files to arrays
        $dataProducts = json_decode(file_get_contents($productsFile), true);
        $dataUsers = json_decode(file_get_contents($usersFile), true);

        // Loop through the products data and create Product entities
        foreach ($dataProducts as $item) {
            $product = new Products();
            $product->setName($item['name']);
            $product->setStock($item['stock']);
            $product->setPrice($item['price']);
            $product->setHighLighted($item['high_lighted']);
            $product->setImage($item['image']);

            $manager->persist($product);
        }
        // Loop through the users data and create User entities
        foreach ($dataUsers as $item) {
            $user = new User();
            $user->setEmail($item['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $item['password']));
            $user->setRoles($item['roles']);
            $user->setName($item['name']);
            $user->setIsVerified($item['is_verified']);
            $user->setDeliveryAddress($item['delivery_adress']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
