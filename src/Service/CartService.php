<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\Customer;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

class CartService
{
    private const SESSION_CART_KEY = 'cart_session_id';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartRepository $cartRepository,
        private RequestStack $requestStack,
        private Security $security
    ) {}

    public function getCurrentCart(): Cart
    {
        $user = $this->security->getUser();
        
        if ($user instanceof Customer) {
            return $this->getCustomerCart($user);
        }
        
        return $this->getSessionCart();
    }

    private function getCustomerCart(Customer $customer): Cart
    {
        $cart = $this->cartRepository->findActiveCartByCustomer($customer);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->setCustomer($customer);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    private function getSessionCart(): Cart
    {
        $session = $this->requestStack->getSession();
        $sessionId = $session->get(self::SESSION_CART_KEY);
        
        if (!$sessionId) {
            $sessionId = session_create_id();
            $session->set(self::SESSION_CART_KEY, $sessionId);
        }

        $cart = $this->cartRepository->findActiveCartBySession($sessionId);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->setSessionId($sessionId);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    public function addProduct(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCurrentCart();
        $existingItem = $cart->findItemByProduct($product);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addItem($cartItem);
            $this->entityManager->persist($cartItem);
        }

        $this->entityManager->flush();
    }

    public function removeProduct(Product $product): void
    {
        $cart = $this->getCurrentCart();
        $item = $cart->findItemByProduct($product);

        if ($item) {
            $cart->removeItem($item);
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }
    }

    public function updateQuantity(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeProduct($product);
            return;
        }

        $cart = $this->getCurrentCart();
        $item = $cart->findItemByProduct($product);

        if ($item) {
            $item->setQuantity($quantity);
            $this->entityManager->flush();
        }
    }

    public function clearCart(): void
    {
        $cart = $this->getCurrentCart();
        
        foreach ($cart->getItems() as $item) {
            $this->entityManager->remove($item);
        }
        
        $cart->clear();
        $this->entityManager->flush();
    }

    public function getCartTotal(): float
    {
        $cart = $this->getCurrentCart();
        return $cart->getTotal();
    }

    public function getCartItemCount(): int
    {
        $cart = $this->getCurrentCart();
        return $cart->getTotalItems();
    }

    public function mergeSessionCartWithCustomerCart(Customer $customer): void
    {
        $session = $this->requestStack->getSession();
        $sessionId = $session->get(self::SESSION_CART_KEY);
        
        if (!$sessionId) {
            return;
        }

        $sessionCart = $this->cartRepository->findActiveCartBySession($sessionId);
        
        if (!$sessionCart || $sessionCart->isEmpty()) {
            return;
        }

        $customerCart = $this->getCustomerCart($customer);

        foreach ($sessionCart->getItems() as $sessionItem) {
            $product = $sessionItem->getProduct();
            $existingItem = $customerCart->findItemByProduct($product);

            if ($existingItem) {
                $existingItem->setQuantity($existingItem->getQuantity() + $sessionItem->getQuantity());
            } else {
                $cartItem = new CartItem();
                $cartItem->setProduct($product);
                $cartItem->setQuantity($sessionItem->getQuantity());
                $customerCart->addItem($cartItem);
                $this->entityManager->persist($cartItem);
            }
        }

        // Remove session cart
        foreach ($sessionCart->getItems() as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->remove($sessionCart);
        $session->remove(self::SESSION_CART_KEY);

        $this->entityManager->flush();
    }
}