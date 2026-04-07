<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function search(array $filters): array
    {
        $qb = $this->createQueryBuilder('p');

        if (!empty($filters['name'])) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%'.$filters['name'].'%');
        }
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $filters['category']);
        }
        if (!empty($filters['country'])) {
            $qb->andWhere('p.country = :country')
                ->setParameter('country', $filters['country']);
        }
        if (!empty($filters['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
                ->setParameter('max_price', $filters['max_price']);
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLowStock(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock < p.stockThreshold')
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }
}