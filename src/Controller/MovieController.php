<?php

namespace App\Controller;


use App\Repository\MovieRepository;
use App\Domain\MovieDomain;
use App\DataTransfertObject\MovieDto;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Doctrine\ORM\EntityManagerInterface;
use App\Traits\ChangeStatusTrait;

#[Route('/api')]
final class MovieController extends AbstractController
{
    use ChangeStatusTrait;


    #[Route('/movie', name: 'app_movie_index', methods: ['GET'])]
    public function index(
        MovieRepository $movieRepository
    ): ?JsonResponse
    {
        $movies = $movieRepository->findAll();

        return $this->json($movies, Response::HTTP_OK, [], ['groups' => ['movie:read']]);
    }

    #[Route('/movie/{id}', name: 'app_movie_show', methods: ['GET'])]
    public function show(
        int $id,
        MovieDomain $movieDomain
    ): JsonResponse
    {
        $movieToShow = $movieDomain->findMovieById($id);
        return $this->json($movieToShow, Response::HTTP_OK, [], ['groups' => ['movie:read']]);
    }

    #[Route('/movie', name: 'app_movie_create' , methods: ['POST'])]
    public function create(
        MovieDomain $movieDomain,
        #[MapRequestPayload] MovieDto $movieDto
    ): JsonResponse
    {
        $newMovie = $movieDomain->register($movieDto);
        return $this->json($newMovie, Response::HTTP_CREATED, [], ['groups' => ['movie:read']]);
    }

    #[Route('/movie/{id}', name: 'app_movie_update', methods: ['PUT'])]
    public function update(
        int $id,
        MovieDomain $movieDomain,
        #[MapRequestPayload] MovieDto $movieDto
    ): JsonResponse
    {
        $movieToUpdate = $movieDomain->findMovieById($id);

        $updatedMovie = $movieDomain->updateMovie($movieToUpdate, $movieDto);

        return $this->json($updatedMovie, Response::HTTP_OK, [], ['groups' => ['movie:read']]);
    }

    #[Route('/movie/{id}', name: 'app_movie_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        MovieDomain $movieDomain,
    ): JsonResponse
    {
        $movieToDelete = $movieDomain->findMovieById($id);
        $movieDomain->removeMovie($movieToDelete);

        return $this->json(['message' => 'Movie deleted successfully']);
    }

    #[Route('/movie/{id}/publish', name: 'app_movie_publish', methods: ['POST'])]
    public function publishMovie(
        int $id, 
        #[Autowire(service: 'state_machine.product')]
        WorkflowInterface $productWorkflow,
        MovieRepository $movieRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $movie = $movieRepository->find($id);
        
        $this->changeStatut($productWorkflow, $movie, $entityManager, 'approve');
        
        return new JsonResponse(['status' => $movie->getStatus()]);
    }

    #[Route('/movie/{id}/out-of-stock', name: 'app_movie_out_of_stock', methods: ['POST'])]
    public function putOutOfStockMovie(
        int $id, 
        #[Autowire(service: 'state_machine.product')]
        WorkflowInterface $productWorkflow,
        MovieRepository $movieRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $movie = $movieRepository->find($id);

        $this->changeStatut($productWorkflow, $movie, $entityManager ,'mark_out_of_stock');
        
        return new JsonResponse(['status' => $movie->getStatus()]);
    }

}
