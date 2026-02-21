<?php

namespace App\Domain;

use App\Entity\Book;
use App\DataTransfertObject\BookDto;
use App\Mapper\BookMapper;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookDomain 
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookRepository $bookRepository
    ){}

    public function register(
        BookDto $bookDto,
    ): Book
    {
        $book = BookMapper::fromDto($bookDto);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    public function findBookById(
        int $id,
    ): Book
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            throw new NotFoundHttpException('Book not found');
        }
        return $book;
    }

    public function removeBook(Book $bookToDelete): void
    {
        $this->entityManager->remove($bookToDelete);
        $this->entityManager->flush();
    }

    public function updateBook(Book $bookToUpdate, BookDto $bookDto): Book
    {
        $bookToUpdate->setName($bookDto->name);
        $bookToUpdate->setDescription($bookDto->description);
        $bookToUpdate->setPrice($bookDto->price);
        $bookToUpdate->setQuantity($bookDto->quantity);
        $bookToUpdate->setImageUrl($bookDto->imageUrl);

        $this->entityManager->flush();

        return $bookToUpdate;
    }

}