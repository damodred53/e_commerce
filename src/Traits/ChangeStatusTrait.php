<?php

namespace App\Traits;

use Symfony\Component\Workflow\WorkflowInterface;
use Doctrine\ORM\EntityManagerInterface;

trait ChangeStatusTrait
{
    public function changeStatut(
        WorkflowInterface $productWorkflow, 
        $product, 
        EntityManagerInterface $entityManager,
        string $status
        ): void
    {
        if ($productWorkflow->can($product, $status)) {
            $productWorkflow->apply($product, $status);
            $entityManager->flush();
        }
    }
}