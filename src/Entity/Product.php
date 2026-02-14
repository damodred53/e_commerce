<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\AbstractClass\AbstractProduct;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product extends AbstractProduct
{
    
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}
