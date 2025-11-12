<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findActiveCartByCustomer(Customer $customer): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->andWhere('c.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveCartBySession(string $sessionId): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->andWhere('c.sessionId = :sessionId')
            ->andWhere('c.customer IS NULL')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCartsWithItems(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->leftJoin('c.customer', 'customer')
            ->andWhere('i.id IS NOT NULL')
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAbandonedCarts(\DateTimeInterface $olderThan): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->andWhere('c.updatedAt < :olderThan')
            ->andWhere('i.id IS NOT NULL')
            ->setParameter('olderThan', $olderThan)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}