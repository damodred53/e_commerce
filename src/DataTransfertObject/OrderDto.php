<?php

namespace App\DataTransfertObject;

use Symfony\Component\Validator\Constraints as Assert;

class OrderDto
{
    #[Assert\NotBlank(message: "Le status de la commande est obligatoire")]
    #[Assert\Type(type: 'array')]
    #[Assert\Count(min: 1, minMessage: "La commande doit contenir au moins un item")]
    public array $items = [];
}