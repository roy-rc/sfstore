<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findRecentOrders(int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOrdersByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOrdersByCustomer($customer): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOrderByNumber(string $orderNumber): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.orderNumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getOrderStats(): array
    {
        $qb = $this->createQueryBuilder('o');
        
        $total = $qb->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $pending = $qb->select('COUNT(o.id)')
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        $processing = $qb->select('COUNT(o.id)')
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::STATUS_PROCESSING)
            ->getQuery()
            ->getSingleScalarResult();

        $delivered = $qb->select('COUNT(o.id)')
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::STATUS_DELIVERED)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'delivered' => $delivered,
        ];
    }
}