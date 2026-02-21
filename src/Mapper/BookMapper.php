<?php

namespace App\Mapper;

use App\Entity\Book;
use App\DataTransfertObject\BookDto;

class BookMapper
{
    public static function fromDto(BookDto $bookDto): Book
    {
        $book = new Book();
        $book->setName($bookDto->name);
        $book->setPrice($bookDto->price);
        $book->setDescription($bookDto->description);
        $book->setQuantity($bookDto->quantity);
        $book->setImageUrl($bookDto->imageUrl);
        $book->setAuthor($bookDto->author);
        $book->setPages($bookDto->pages);

        return $book;
    }
}