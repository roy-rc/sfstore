<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/{slug}', name: 'category_show')]
    public function show(
        string $slug,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ): Response {
        $category = $categoryRepository->findBySlug($slug);
        
        if (!$category) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }

        $products = $productRepository->findByCategory($category);
        $subcategories = $categoryRepository->findSubcategories($category);

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
            'subcategories' => $subcategories,
        ]);
    }
}