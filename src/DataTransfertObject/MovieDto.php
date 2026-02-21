<?php

namespace App\DataTransfertObject;

use Symfony\Component\Validator\Constraints as Assert;

class MovieDto
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

    #[Assert\NotBlank(message: "La durée du film est obligatoire")]
    #[Assert\Type(type: 'integer')]
    public ?int $duration;

    #[Assert\NotBlank(message: "La date de sortie du film est obligatoire")]
    #[Assert\Type(type: 'string')]
    public ?string $releaseDate = null;

    #[Assert\NotNull(message: "Il faut indiquer si le film est pour les plus de 18 ans ou pas")]
    #[Assert\Type(type: 'boolean')] 
    public ?bool $isOver18 = null;

}