<?php

namespace App\Mapper;

use App\Entity\Movie;
use App\DataTransfertObject\MovieDto;

class MovieMapper
{
    public static function fromDto(MovieDto $movieDto): Movie
    {
        $movie = new Movie();
        $movie->setName($movieDto->name);
        $movie->setPrice($movieDto->price);
        $movie->setDescription($movieDto->description);
        $movie->setQuantity($movieDto->quantity);
        $movie->setImageUrl($movieDto->imageUrl);
        $movie->setDuration($movieDto->duration);
        $movie->setReleaseDate(new \DateTimeImmutable($movieDto->releaseDate));
        $movie->setIsOver18($movieDto->isOver18);

        return $movie;
    }
}