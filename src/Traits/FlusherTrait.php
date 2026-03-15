<?php

namespace App\Traits;

use Doctrine\ORM\EntityManagerInterface;

trait FlusherTrait
{
    public function flusher(
        $product, 
        EntityManagerInterface $entityManager,
        ): void
    {
        $entityManager->persist($product);
        $entityManager->flush();
    }
}