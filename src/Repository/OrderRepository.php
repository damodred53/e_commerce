<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByUserWithItems(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderItems','oi')
            ->addSelect('oi')
            ->where('o.utilisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByIdAndUserWithItems(User $user, int $orderId): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderItems', 'oi')
            ->addSelect('oi')
            ->andWhere('o.id = :orderId')
            ->andWhere('o.utilisateur = :user')
            ->setParameter('orderId', $orderId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
