<?php

namespace App\Controller\Admin;

use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/carts')]
class AdminCartController extends AbstractController
{
    #[Route('/', name: 'admin_carts_index')]
    public function index(CartRepository $cartRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $cartsWithItems = $cartRepository->findCartsWithItems();
        
        return $this->render('admin/cart/index.html.twig', [
            'carts' => $cartsWithItems,
        ]);
    }
}