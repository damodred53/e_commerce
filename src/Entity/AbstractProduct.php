<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap([
    "book" => Book::class,
    "movie" => Movie::class
])]
abstract class AbstractProduct
{

    /**
     * @var int    
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?int $quantity = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 100)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?string $price = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => 'draft'])]
    #[Groups(['movie:read', 'movie:write', 'book:read', 'book:write'])]
    private string $status = 'draft';

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $orderItems;

    #[ORM\OneToOne(mappedBy: 'product', targetEntity: Document::class, cascade: ['persist', 'remove'])]
    private ?Document $document = null;

    #[Assert\File(
        maxSize: '10M',
        mimeTypes: ["application/pdf", "text/plain"],
        mimeTypesMessage: 'Seuls les fichiers PDF et TXT sont autorisés.'
    )]
    private ?UploadedFile $documentFile = null;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            return $this;
        }

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        // unset the owning side of the relation if necessary
        if ($document === null && $this->document !== null) {
            $this->document->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if ($document !== null && $document->getProduct() !== $this) {
            $document->setProduct($this);
        }

        $this->document = $document;

        return $this;
    }

    public function getDocumentFile(): ?UploadedFile
    {
        return $this->documentFile;
    }

    public function setDocumentFile(?UploadedFile $documentFile): static
    {
        $this->documentFile = $documentFile;

        return $this;
    }
}