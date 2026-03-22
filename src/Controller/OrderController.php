<?php

namespace App\Controller;

use App\DataTransfertObject\OrderDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Domain\OrderDomain;
use App\Entity\User;

#[Route('/api')]
final class OrderController extends AbstractController
{
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
