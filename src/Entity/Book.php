<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book extends AbstractProduct
{

    #[ORM\Column(length: 50)]
    #[Groups(['book:read', 'book:write'])]
    private ?string $author = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['book:read', 'book:write'])]
    private ?int $pages = null;

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(int $pages): static
    {
        $this->pages = $pages;

        return $this;
    }
}
