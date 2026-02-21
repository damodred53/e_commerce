<?php

namespace App\Domain;

use App\Entity\Movie;
use App\DataTransfertObject\MovieDto;
use App\Mapper\MovieMapper;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MovieDomain 
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MovieRepository $movieRepository
    ){}

    public function register(
        MovieDto $movieDto,
    ): Movie
    {
        $movie = MovieMapper::fromDto($movieDto);

        $this->entityManager->persist($movie);
        $this->entityManager->flush();

        return $movie;
    }

    public function findMovieById(
        int $id,
    ): Movie
    {
        $movie = $this->movieRepository->find($id);

        if (!$movie) {
            throw new NotFoundHttpException('Movie not found');
        }
        return $movie;
    }

    public function removeMovie(Movie $movieToDelete): void
    {
        $this->entityManager->remove($movieToDelete);
        $this->entityManager->flush();
    }

    public function updateMovie(Movie $movieToUpdate, MovieDto $movieDto): Movie
    {
        $movieToUpdate->setName($movieDto->name);
        $movieToUpdate->setDescription($movieDto->description);
        $movieToUpdate->setPrice($movieDto->price);
        $movieToUpdate->setQuantity($movieDto->quantity);
        $movieToUpdate->setImageUrl($movieDto->imageUrl);
        $movieToUpdate->setDuration($movieDto->duration);
        $movieToUpdate->setReleaseDate(new \DateTimeImmutable($movieDto->releaseDate));
        $movieToUpdate->setIsOver18($movieDto->isOver18);

        $this->entityManager->flush();

        return $movieToUpdate;
    }

}