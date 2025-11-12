<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'checkout_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $cart = $this->cartService->getCurrentCart();

        if ($cart->isEmpty()) {
            $this->addFlash('warning', 'Tu carrito está vacío');
            return $this->redirectToRoute('cart_index');
        }

        // Check stock availability
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if (!$product->isAvailable() || $product->getStock() < $item->getQuantity()) {
                $this->addFlash('error', sprintf(
                    'El producto "%s" no tiene suficiente stock disponible',
                    $product->getName()
                ));
                return $this->redirectToRoute('cart_index');
            }
        }

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/process', name: 'checkout_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $cart = $this->cartService->getCurrentCart();
        $customer = $this->getUser();

        if ($cart->isEmpty()) {
            $this->addFlash('warning', 'Tu carrito está vacío');
            return $this->redirectToRoute('cart_index');
        }

        try {
            $this->entityManager->beginTransaction();

            // Create order
            $order = new Order();
            $order->setCustomer($customer);

            // Add items to order
            foreach ($cart->getItems() as $cartItem) {
                $product = $cartItem->getProduct();
                
                // Check stock again
                if (!$product->isAvailable() || $product->getStock() < $cartItem->getQuantity()) {
                    throw new \Exception(sprintf(
                        'El producto "%s" no tiene suficiente stock disponible',
                        $product->getName()
                    ));
                }

                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($cartItem->getQuantity());
                $order->addItem($orderItem);

                // Reduce stock
                $product->setStock($product->getStock() - $cartItem->getQuantity());

                $this->entityManager->persist($orderItem);
                $this->entityManager->persist($product);
            }

            // Calculate total
            $order->calculateTotal();
            
            $this->entityManager->persist($order);

            // Clear cart
            $this->cartService->clearCart();

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->addFlash('success', sprintf(
                'Tu orden #%s ha sido procesada exitosamente',
                $order->getOrderNumber()
            ));

            return $this->redirectToRoute('checkout_success', [
                'orderNumber' => $order->getOrderNumber()
            ]);

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $this->addFlash('error', 'Error al procesar la orden: ' . $e->getMessage());
            
            return $this->redirectToRoute('checkout_index');
        }
    }

    #[Route('/success/{orderNumber}', name: 'checkout_success')]
    public function success(string $orderNumber): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        return $this->render('checkout/success.html.twig', [
            'order_number' => $orderNumber,
        ]);
    }
}