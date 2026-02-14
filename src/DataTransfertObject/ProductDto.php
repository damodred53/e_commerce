<?php

namespace App\DataTransfertObject;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDto
{

    #[Assert\NotBlank(message: "Le nom du produit est obligatoire")]
    #[Assert\Type(type: 'string')]
    public string $name;

    #[Assert\NotBlank(message: "Le prix du produit est obligatoire")]
    #[Assert\Type(type: 'string')]
    public string $price;

    #[Assert\NotBlank(message: "La description du produit est obligatoire")]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        min: 1,
        max: 1000,
        minMessage: "Le message ne peut pas être vide",
        maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères"
    )]
    public string $description;

    #[Assert\NotBlank(message: "La quantité du produit est obligatoire")]
    #[Assert\Type(type: 'integer')]
    public ?int $quantity;

    public ?string $imageUrl;


}