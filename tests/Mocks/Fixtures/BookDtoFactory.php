<?php

namespace App\Tests\Mocks\Fixtures;

use App\DataTransfertObject\BookDto;

class BookDtoFactory
{

    public static function create(array $overrides = []) : BookDto
    {
        $defaultBook = [
            'name' => 'Clean Code',
            'price' => '19.99',
            'description' => 'A Handbook of Agile Software Craftsmanship',
            'quantity' => 12,
            'imageUrl' => 'https://example.com/clean-code.jpg',
            'author' => 'Robert C. Martin',
            'pages' => 464,
        ];

        $data = array_merge($defaultBook, $overrides);

        $bookDto = new BookDto();

        $bookDto->name = $data['name'];
        $bookDto->price = $data['price'];
        $bookDto->description = $data['description'];
        $bookDto->quantity = $data['quantity'];
        $bookDto->imageUrl = $data['imageUrl'];
        $bookDto->author = $data['author'];
        $bookDto->pages = $data['pages'];

        return $bookDto;

    }

}