<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $productsFile = $projectRoot . '/src/DataFixtures/data/products.json';
        $usersFile = $projectRoot . '/src/DataFixtures/data/users.json';
        $dataProducts = json_decode(file_get_contents($productsFile), true);
        $dataUsers = json_decode(file_get_contents($usersFile), true);

        foreach ($dataProducts as $item) {
            $product = new Products();
            $product->setName($item['name']);
            $product->setStock($item['stock']);
            $product->setPrice($item['price']);
            $product->setHighLighted($item['high_lighted']);
            $product->setImage($item['image']);

            $manager->persist($product);
        }

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
