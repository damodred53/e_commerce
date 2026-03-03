<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie extends AbstractProduct
{

    #[ORM\Column]
    #[Groups(['movie:read', 'movie:write'])]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['movie:read', 'movie:write'])]
    private ?\DateTimeImmutable $releaseDate = null;

    #[ORM\Column]
    #[Groups(['movie:read', 'movie:write'])]
    private ?bool $isOver18 = null;

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeImmutable $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function isOver18(): ?bool
    {
        return $this->isOver18;
    }

    public function setIsOver18(bool $isOver18): static
    {
        $this->isOver18 = $isOver18;

        return $this;
    }
}
