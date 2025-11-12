<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findActiveCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMainCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSubcategories(Category $parent): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent = :parent')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('parent', $parent)
            ->setParameter('isActive', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('slug', $slug)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCategoriesWithProducts(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.products', 'p')
            ->andWhere('c.isActive = :isActive')
            ->andWhere('p.isActive = :productActive')
            ->setParameter('isActive', true)
            ->setParameter('productActive', true)
            ->groupBy('c.id')
            ->having('COUNT(p.id) > 0')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMainCategoriesWithSubcategories(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'sub')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('sub.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}