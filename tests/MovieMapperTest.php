<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\DataTransfertObject\MovieDto;
use App\Mapper\MovieMapper;
use App\Entity\Movie;
use App\Tests\Mocks\Fixtures\MovieDtoFactory;

class MovieMapperTest extends TestCase
{
    public function testFromDtoMapsAllPropertiesCorrectly(): void
    {
        $movieDto = MovieDtoFactory::create();

        $movie = MovieMapper::fromDto($movieDto);

        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('Inception', $movie->getName());
        $this->assertEquals('9.99', $movie->getPrice());
        $this->assertEquals('Un film de science-fiction', $movie->getDescription());
        $this->assertEquals(50, $movie->getQuantity());
        $this->assertEquals('https://example.com/inception.jpg', $movie->getImageUrl());
        $this->assertEquals(148, $movie->getDuration());
        $this->assertEquals(new \DateTimeImmutable('2010-07-16'), $movie->getReleaseDate());
        $this->assertEquals(true, $movie->isOver18());

    }

    public function testFromDtoThrowsOnInvalidReleaseDate(): void
    {
        $dto = MovieDtoFactory::create(['releaseDate' => 'blablabla']);
        $this->expectException(\Exception::class);
        MovieMapper::fromDto($dto);
    }

}
