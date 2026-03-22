<?php

namespace App\Domain;

use App\DataTransfertObject\OrderDto;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

final class OrderDomain
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookRepository $bookRepository,
        private MovieRepository $movieRepository,
    ){}

    public function createOrder(OrderDto $orderDto, User $user) : Order
    {
        $order = new Order();
        $order->setUtilisateur($user);
        $order->setStatus('pending');

        // $total = '0.00';

        foreach ($orderDto->items as $item) {

            $productId = $item['productId'] ?? null;
            $quantity = $item['quantity'] ?? null;
        
            if(!$productId || !$quantity || $quantity<=0) {
                throw new \InvalidArgumentException(
                    'productId et quantité doivent être renseignés. La quanttié doit être supérieur à 0'
                );
            }

            $product = $this->bookRepository->find($productId) ?? $this->movieRepository->find($productId);
            if(!$product) {
                throw new \InvalidArgumentException("Produit avec l'ID : {$productId} introuvable");
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setProductName($product->getName());
            $orderItem->setPrice($product->getPrice());
            $orderItem->setCommande($order);

            $order->addOrderItem($orderItem);
            
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}