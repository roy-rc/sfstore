<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $featuredProducts = $productRepository->findFeaturedProducts(8);
        $mainCategories = $categoryRepository->findMainCategoriesWithSubcategories();

        return $this->render('home/index.html.twig', [
            'featured_products' => $featuredProducts,
            'main_categories' => $mainCategories,
        ]);
    }
}