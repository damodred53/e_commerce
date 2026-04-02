<?php

namespace App\Controller;

use App\DataTransfertObject\OrderDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Domain\OrderDomain;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;

#[Route('/api')]
final class OrderController extends AbstractController
{

    #[Route('/order/me', name:'app_order_show', methods: ['GET'])]
    public function getAllOrders(
        OrderDomain $orderDomain, 
        OrderRepository $orderRepository
    ): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['message' => 'Vous devez être authentifié'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $orders = $orderRepository->findByUserWithItems($user);

        return $this->json(
            $orders,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']]
        );
    }

    #[Route('/order/me/{id}', name:'app_order_detail', methods: ['GET'])]
    public function getOrderById(
        OrderDomain $orderDomain, 
        OrderRepository $orderRepository,
        int $id
    ): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['message' => 'Vous devez être authentifié'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $order = $orderRepository->findOneByIdAndUserWithItems($user, $id );

        if ($order === null) {
            return $this->json(
                ['message' => 'Commande introuvable'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $order,
            Response::HTTP_OK,
            [],
            ['groups' => ['order:read']]
        );
    }


    #[Route('/order', name: 'app_order_create', methods: ['POST'])]
    public function create(
        OrderDomain $orderDomain,
        #[MapRequestPayload] OrderDto $orderDto,
    ): Response
    {
        
    $user = $this->getUser();

    // dump('utilisateur de ouf: ', $user->getRoles(), $user->getUserIdentifier());

    if (!$user instanceof User) {
        return $this->json(
            ['message' => 'Vous devez être authentifié'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    try {
        $order = $orderDomain->createOrder($orderDto, $user);

        return $this->json(
            $order,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['order:read']]
        );
    
    } catch (\InvalidArgumentException $error) {
        return $this->json(
            ['message' => $error->getMessage()],
            Response::HTTP_BAD_REQUEST
        );

    } catch (\Exception $error) {
        return $this->json(
            ['message' => $error->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}
