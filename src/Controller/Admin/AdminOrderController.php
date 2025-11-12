<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/orders')]
class AdminOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_orders_index')]
    public function index(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $orders = $orderRepository->findRecentOrders(50);
        $stats = $orderRepository->getOrderStats();
        
        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'admin_orders_show')]
    public function show(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/update-status', name: 'admin_orders_update_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $newStatus = $request->get('status');
        $validStatuses = array_keys(Order::getAvailableStatuses());

        if (!in_array($newStatus, $validStatuses)) {
            $this->addFlash('error', 'Estado inválido');
            return $this->redirectToRoute('admin_orders_show', ['id' => $order->getId()]);
        }

        try {
            $order->setStatus($newStatus);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf(
                'Estado de la orden #%s actualizado a %s',
                $order->getOrderNumber(),
                $order->getStatusLabel()
            ));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al actualizar el estado de la orden');
        }

        return $this->redirectToRoute('admin_orders_show', ['id' => $order->getId()]);
    }
}