<?php

namespace App\Tests\Mocks\Fixtures;

use App\DataTransfertObject\MovieDto;

class MovieDtoFactory
{
    public static function create(array $overrides = []): MovieDto
    {
        $defaults = [
            'name' => 'Inception',
            'price' => '9.99',
            'description' => 'Un film de science-fiction',
            'quantity' => 50,
            'imageUrl' => 'https://example.com/inception.jpg',
            'duration' => 148,
            'releaseDate' => '2010-07-16',
            'isOver18' => true,
        ];

        $data = array_merge($defaults, $overrides);

        $dto = new MovieDto();
        $dto->name = $data['name'];
        $dto->price = $data['price'];
        $dto->description = $data['description'];
        $dto->quantity = $data['quantity'];
        $dto->imageUrl = $data['imageUrl'];
        $dto->duration = $data['duration'];
        $dto->releaseDate = $data['releaseDate'];
        $dto->isOver18 = $data['isOver18'];

        return $dto;
    }
}