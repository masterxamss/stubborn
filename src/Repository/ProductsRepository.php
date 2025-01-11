<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function findLowStockProducts()
    {
        $sql = "
            SELECT p.*
            FROM products p
            WHERE JSON_UNQUOTE(JSON_EXTRACT(p.stock, '$.XS')) < 3
            OR JSON_UNQUOTE(JSON_EXTRACT(p.stock, '$.S')) < 3
            OR JSON_UNQUOTE(JSON_EXTRACT(p.stock, '$.M')) < 3
            OR JSON_UNQUOTE(JSON_EXTRACT(p.stock, '$.L')) < 3
            OR JSON_UNQUOTE(JSON_EXTRACT(p.stock, '$.XL')) < 3
        ";

        // Create ResultSetMapping
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Products::class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'name', 'name');
        $rsm->addFieldResult('p', 'stock', 'stock');
        $rsm->addFieldResult('p', 'image', 'image');
        $rsm->addFieldResult('p', 'price', 'price');

        // Execute Native Query
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
}
