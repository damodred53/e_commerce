<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $commande = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AbstractProduct $product = null;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $quantity = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read'])]
    private ?string $productName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Order
    {
        return $this->commande;
    }

    public function setCommande(?Order $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduct(): ?AbstractProduct
    {
        return $this->product;
    }

    public function setProduct(?AbstractProduct $product): static
    {
        $this->product = $product;

        if ($product !== null && $this->productName === null) {
            $this->productName = $product->getName();
        }

        if ($product !== null && $this->price === null) {
            $this->price = $product->getPrice();
        }

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        if ($this->commande !== null) {
            $this->commande->recalculateTotal();
        }

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        if ($this->commande !== null) {
            $this->commande->recalculateTotal();
        }

        return $this;
    }

    public function getSubtotal(): string
    {
        $price = (float) ($this->price ?? '0');
        $quantity = $this->quantity ?? 0;

        return number_format($price * $quantity, 2, '.', '');
    }
}
