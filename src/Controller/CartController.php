<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private ProductRepository $productRepository
    ) {}

    #[Route('/', name: 'cart_index')]
    public function index(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): JsonResponse
    {
        $quantity = max(1, (int) $request->get('quantity', 1));

        if (!$product->isAvailable()) {
            return $this->json([
                'success' => false,
                'message' => 'Producto no disponible'
            ], 400);
        }

        if ($quantity > $product->getStock()) {
            return $this->json([
                'success' => false,
                'message' => 'No hay suficiente stock disponible'
            ], 400);
        }

        try {
            $this->cartService->addProduct($product, $quantity);

            return $this->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cartCount' => $this->cartService->getCartItemCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al agregar producto al carrito'
            ], 500);
        }
    }

    #[Route('/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(Product $product): JsonResponse
    {
        try {
            $this->cartService->removeProduct($product);

            return $this->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'cartCount' => $this->cartService->getCartItemCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar producto del carrito'
            ], 500);
        }
    }

    #[Route('/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request): JsonResponse
    {
        $quantity = max(0, (int) $request->get('quantity', 0));

        if ($quantity > $product->getStock()) {
            return $this->json([
                'success' => false,
                'message' => 'No hay suficiente stock disponible'
            ], 400);
        }

        try {
            $this->cartService->updateQuantity($product, $quantity);

            return $this->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'cartCount' => $this->cartService->getCartItemCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar cantidad'
            ], 500);
        }
    }

    #[Route('/clear', name: 'cart_clear', methods: ['POST'])]
    public function clear(): JsonResponse
    {
        try {
            $this->cartService->clearCart();

            return $this->json([
                'success' => true,
                'message' => 'Carrito vaciado',
                'cartCount' => 0,
                'cartTotal' => 0
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al vaciar carrito'
            ], 500);
        }
    }

    #[Route('/count', name: 'cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json([
            'count' => $this->cartService->getCartItemCount(),
            'total' => $this->cartService->getCartTotal()
        ]);
    }
}