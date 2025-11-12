<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/{slug}', name: 'product_show')]
    public function show(
        string $slug,
        ProductRepository $productRepository
    ): Response {
        $product = $productRepository->findBySlug($slug);
        
        if (!$product) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        $relatedProducts = $productRepository->findRelatedProducts($product);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'related_products' => $relatedProducts,
        ]);
    }
}