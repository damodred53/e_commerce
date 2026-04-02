<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['movie:read', 'book:read'])]
    private ?string $originalName = null;

    #[ORM\Column(length: 100)]
    private ?string $storedName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['movie:read','book:read'])]
    private ?string $mimeType = null;

    #[ORM\Column]
    #[Groups(['movie:read','book:read'])]
    private ?int $size = null;

    #[ORM\Column(length: 120)]
    private ?string $path = null;

    #[ORM\Column]
    #[Groups(['movie:read','book:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'document', targetEntity: AbstractProduct::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?AbstractProduct $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getStoredName(): ?string
    {
        return $this->storedName;
    }

    public function setStoredName(string $storedName): static
    {
        $this->storedName = $storedName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

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

    public function getProduct(): ?AbstractProduct
    {
        return $this->product;
    }

    public function setProduct(?AbstractProduct $product): static
    {
        $this->product = $product;

        return $this;
    }
    public function getDownloadUrl(): ?string
    {
        if ($this->id === null) {
            return null;
        }

        return sprintf('/api/document/%d/download', $this->id);
    }
}
