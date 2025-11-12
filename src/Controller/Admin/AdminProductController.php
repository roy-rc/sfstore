<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/products')]
class AdminProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {}

    #[Route('/', name: 'admin_products_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'admin_products_new')]
    public function new(Request $request, CategoryRepository $categoryRepository, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Product();
        $categories = $categoryRepository->findBy(['isActive' => true], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $product->setName($request->get('name'));
            $product->setSku($request->get('sku'));
            $product->setDescription($request->get('description'));
            $product->setPrice($request->get('price'));
            $product->setStock((int) $request->get('stock'));
            $product->setImage($request->get('image'));
            $product->setIsActive((bool) $request->get('isActive'));

            // Generate slug
            $slug = $this->slugger->slug($product->getName())->lower();
            $product->setSlug($slug);

            // Handle categories
            $selectedCategories = $request->get('categories', []);
            foreach ($selectedCategories as $categoryId) {
                $category = $categoryRepository->find($categoryId);
                if ($category) {
                    $product->addCategory($category);
                }
            }

            // Validate
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                try {
                    $this->entityManager->persist($product);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Producto creado exitosamente');
                    return $this->redirectToRoute('admin_products_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al crear el producto: ' . $e->getMessage());
                }
            }
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_products_edit')]
    public function edit(Product $product, Request $request, CategoryRepository $categoryRepository, ProductRepository $productRepository, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = $categoryRepository->findBy(['isActive' => true], ['name' => 'ASC']);
        $allProducts = $productRepository->findBy(['isActive' => true]);

        if ($request->isMethod('POST')) {
            $product->setName($request->get('name'));
            $product->setSku($request->get('sku'));
            $product->setDescription($request->get('description'));
            $product->setPrice($request->get('price'));
            $product->setStock((int) $request->get('stock'));
            $product->setImage($request->get('image'));
            $product->setIsActive((bool) $request->get('isActive'));

            // Update slug if name changed
            $slug = $this->slugger->slug($product->getName())->lower();
            $product->setSlug($slug);
            $product->setUpdatedAt(new \DateTimeImmutable());

            // Clear and add categories
            $product->getCategories()->clear();
            $selectedCategories = $request->get('categories', []);
            foreach ($selectedCategories as $categoryId) {
                $category = $categoryRepository->find($categoryId);
                if ($category) {
                    $product->addCategory($category);
                }
            }

            // Handle related products
            $product->getRelatedProducts()->clear();
            $selectedRelatedProducts = $request->get('relatedProducts', []);
            foreach ($selectedRelatedProducts as $relatedProductId) {
                $relatedProduct = $productRepository->find($relatedProductId);
                if ($relatedProduct && $relatedProduct !== $product) {
                    $product->addRelatedProduct($relatedProduct);
                }
            }

            // Validate
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                try {
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Producto actualizado exitosamente');
                    return $this->redirectToRoute('admin_products_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al actualizar el producto: ' . $e->getMessage());
                }
            }
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'all_products' => $allProducts,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_products_delete', methods: ['POST'])]
    public function delete(Product $product): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Producto eliminado exitosamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar el producto');
        }

        return $this->redirectToRoute('admin_products_index');
    }

    #[Route('/{id}', name: 'admin_products_show')]
    public function show(Product $product): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }
}