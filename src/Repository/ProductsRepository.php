<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    public function findByPriceRange(?string $priceRange): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        if ($priceRange) {
            switch ($priceRange) {
                case '10-29':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 10)
                                 ->setParameter('max', 29);
                    break;
                case '30-35':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 30)
                                 ->setParameter('max', 35);
                    break;
                case '35-50':
                    $queryBuilder->andWhere('p.price BETWEEN :min AND :max')
                                 ->setParameter('min', 35)
                                 ->setParameter('max', 50);
                    break;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

}
