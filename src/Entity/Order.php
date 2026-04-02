<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    #[Groups(['order:read'])]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    #[Groups(['order:read'])]
    private string $total = '0.00';

    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'commande', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(('order:read'))]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setCommande($this);
            $this->recalculateTotal();
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getCommande() === $this) {
                $orderItem->setCommande(null);
            }

            $this->recalculateTotal();
        }

        return $this;
    }

    public function recalculateTotal(): static
    {
        $total = 0.0;

        foreach ($this->orderItems as $orderItem) {
            $price = (float) ($orderItem->getPrice() ?? '0');
            $quantity = $orderItem->getQuantity() ?? 0;
            $total += $price * $quantity;
        }

        $this->total = number_format($total, 2, '.', '');

        return $this;
    }


    public function getOrderItemsTable(): string
    {
        if ($this->orderItems->isEmpty()) {
            return '<p>Aucun article dans cette commande.</p>';
        }

        $rows = array_map(function ($item) {
            return sprintf(
                '<tr>
                    <td>%s</td>
                    <td>%d</td>
                    <td>%s EUR</td>
                    <td>%s EUR</td>
                </tr>',
                htmlspecialchars((string) $item->getProductName(), ENT_QUOTES, 'UTF-8'),
                $item->getQuantity(),
                htmlspecialchars((string) $item->getPrice(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $item->getSubtotal(), ENT_QUOTES, 'UTF-8')
            );
        }, $this->orderItems->toArray());

        return sprintf(
            '<table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantite</th>
                        <th>Prix unitaire</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>%s</tbody>
            </table>',
            implode('', $rows)
        );
    }
}
