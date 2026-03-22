<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MovieRepository;
use App\Domain\MovieDomain;
use App\Tests\Mocks\Fixtures\MovieDtoFactory;
use App\Entity\Movie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PHPUnit\Framework\MockObject\Stub;

class MovieDomainTest extends TestCase
{
    private EntityManagerInterface $em;
     /** @var Stub&MovieRepository */
    private MovieRepository $repo;
    
   

    protected function setUp(): void
    {
        $this->em = $this->createStub(EntityManagerInterface::class);
        $this->repo = $this->createStub(MovieRepository::class);
    }

    public function testRegisterPersistAndFlushMovie(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $domain = new MovieDomain($entityManager, $this->repo);

        $movieDto = MovieDtoFactory::create();
        $movie = $domain->register($movieDto);

        $this->assertInstanceOf(Movie::class, $movie);
    }

    public function testFindMovieByIdReturnsMovie(): void
    {
        $movie = new Movie();
        $this->repo->method('find')->willReturn($movie);

        $domain = new MovieDomain($this->em, $this->repo);

        $this->assertSame($movie, $domain->findMovieById(1));
    }

    public function testFindMovieByIdThrowsWhenNotFound(): void
    {
        $this->repo->method('find')->willReturn(null);

        $domain = new MovieDomain($this->em, $this->repo);

        $this->expectException(NotFoundHttpException::class);

        $domain->findMovieById(1);
    }

    public function testRemoveMovieRemovesAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $domain = new MovieDomain($entityManager, $this->repo);

        $movieTodelete = new Movie();

        $entityManager->expects($this->once())->method('remove');
        $entityManager->expects($this->once())->method('flush');

        $domain->removeMovie($movieTodelete);
    }


}
