<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findActiveProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(Category $category, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'c')
            ->andWhere('c = :category')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('category', $category)
            ->setParameter('isActive', true)
            ->orderBy('p.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('slug', $slug)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAvailableProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :isActive')
            ->andWhere('p.stock > 0')
            ->setParameter('isActive', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRelatedProducts(Product $product, int $limit = 4): array
    {
        // First try to get manually related products
        $related = $product->getRelatedProducts()->toArray();
        
        if (count($related) >= $limit) {
            return array_slice($related, 0, $limit);
        }

        // If not enough related products, find products from same categories
        $categories = $product->getCategories();
        
        if ($categories->isEmpty()) {
            return array_slice($related, 0, $limit);
        }

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'c')
            ->andWhere('c IN (:categories)')
            ->andWhere('p != :product')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('categories', $categories)
            ->setParameter('product', $product)
            ->setParameter('isActive', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit - count($related));

        $categoryProducts = $qb->getQuery()->getResult();
        
        return array_merge($related, $categoryProducts);
    }

    public function searchProducts(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :query OR p.description LIKE :query OR p.sku LIKE :query')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('isActive', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFeaturedProducts(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :isActive')
            ->andWhere('p.stock > 0')
            ->setParameter('isActive', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}