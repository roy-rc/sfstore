<?php

namespace App\Controller\Admin;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\CartRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        CartRepository $cartRepository,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get statistics
        $totalProducts = count($productRepository->findAll());
        $totalCategories = count($categoryRepository->findAll());
        $totalCustomers = count($customerRepository->findAll());
        $cartsWithItems = $cartRepository->findCartsWithItems();
        
        // Get recent products
        $recentProducts = $productRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'total_customers' => $totalCustomers,
            'total_carts' => count($cartsWithItems),
            'recent_products' => $recentProducts,
            'carts_with_items' => array_slice($cartsWithItems, 0, 10),
        ]);
    }
}