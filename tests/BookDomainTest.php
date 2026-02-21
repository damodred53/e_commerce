<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookRepository;
use App\Domain\BookDomain;
use App\Tests\Mocks\Fixtures\BookDtoFactory;
use App\Entity\Book;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function PHPUnit\Framework\assertInstanceOf;

class BookDomainTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private BookRepository $bookRepository;

    protected function setUp(): void {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->bookRepository = $this->createStub(BookRepository::class);
    }

    public function testRegisterPersistAndFlushBook() {

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('persist');

        $domain = new BookDomain($em, $this->bookRepository);

        $bookDto = BookDtoFactory::create();

        $book = $domain->register($bookDto);

        $this>assertInstanceOf(Book::class, $book);

    }

    public function testFindBookByIdReturnsBook(): void
    {
        $book = new Book();
        $this->bookRepository->method('find')->willReturn($book);

        $domain = new BookDomain($this->entityManager , $this->bookRepository);

        $this->assertSame($book, $domain->findBookById(1));
    }

    public function testFailToReturnQBookByIdReturnsBook(): void
    {
        $this->bookRepository->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $domain = new BookDomain($this->entityManager , $this->bookRepository);

        $domain->findBookById(1);
    }
}