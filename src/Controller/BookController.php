<?php

namespace App\Controller;


use App\Repository\BookRepository;
use App\Domain\BookDomain;
use App\DataTransfertObject\BookDto;
use App\Service\DocumentStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Workflow\WorkflowInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Traits\ChangeStatusTrait;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/api')]
final class BookController extends AbstractController
{
    use ChangeStatusTrait;

    #[Route('/book', name: 'app_book_index', methods: ['GET'])]
    public function index(
        BookRepository $bookRepository
    ): JsonResponse
    {
        $books = $bookRepository->findAll();

        return $this->json($books, Response::HTTP_OK, [], ['groups' => ['book:read']]);
    }

    #[Route('/book/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(
        int $id,
        BookDomain $bookDomain
    ): JsonResponse
    {
        $bookToShow = $bookDomain->findBookById($id);
        return $this->json($bookToShow, Response::HTTP_OK, [], ['groups' => ['book:read']]);
    }

    #[Route('/book', name: 'app_book_create' , methods: ['POST'])]
    public function create(
        BookDomain $bookDomain,
        #[MapRequestPayload] BookDto $bookDto
    ): JsonResponse
    {
        $newBook = $bookDomain->register($bookDto);
        return $this->json($newBook, Response::HTTP_CREATED, []);
    }

    #[Route('/book/{id}', name: 'app_book_update', methods: ['PUT'])]
    public function update(
        int $id,
        BookDomain $bookDomain,
        #[MapRequestPayload] BookDto $bookDto
    ): JsonResponse
    {
        $bookToUpdate = $bookDomain->findBookById($id);

        $updatedBook = $bookDomain->updateBook($bookToUpdate, $bookDto);

        return $this->json($updatedBook, Response::HTTP_OK, []);
    }

    #[Route('/book/{id}', name: 'app_book_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        BookDomain $bookDomain,
    ): JsonResponse
    {
        $bookToDelete = $bookDomain->findBookById($id);
        $bookDomain->removeBook($bookToDelete);

        return $this->json(['message' => 'Book deleted successfully']);
    }

    #[Route('/book/{id}/publish', name: 'app_book_publish', methods: ['POST'])]
    public function publishBook(
        int $id, 
        #[Autowire(service: 'state_machine.product')]
        WorkflowInterface $productWorkflow,
        BookRepository $bookRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $book = $bookRepository->find($id);
        
        $this->changeStatut($productWorkflow, $book, $entityManager, 'approve');
        
        return new JsonResponse(['status' => $book->getStatus()]);
    }

    #[Route('/book/{id}/out-of-stock', name: 'app_book_out_of_stock', methods: ['POST'])]
    public function putOutOfStockBook(
        int $id, 
        #[Autowire(service: 'state_machine.product')]
        WorkflowInterface $productWorkflow,
        BookRepository $bookRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $book = $bookRepository->find($id);

        $this->changeStatut($productWorkflow, $book, $entityManager ,'mark_out_of_stock');
        
        return new JsonResponse(['status' => $book->getStatus()]);
    }

    #[Route('/book/{id}/download-document', name: 'app_book_download_document', methods: ['GET'])]
    public function downloadBook(
        int $id,
        BookDomain $bookDomain,
        DocumentStorage $documentStorage,
    )
    {
        $book = $bookDomain->findBookById($id);
        $document = $book->getDocument();

        if ($document === null || $document->getStoredName() === null) {
            throw $this->createNotFoundException('Aucune fiche technique n est associee a ce livre.');
        }

        $absolutePath = $documentStorage->getAbsolutePath($document->getStoredName());

        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Le fichier associe a ce livre est introuvable.');
        }

        return $this->file(
            $absolutePath,
            $document->getOriginalName(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
