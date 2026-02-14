<?php

namespace App\Mapper;

use App\Entity\Product;
use App\DataTransfertObject\ProductDto;

class ProductMapper
{
    public static function fromDto(ProductDto $productDto): Product
    {
        $product = new Product();
        $product->setName($productDto->name);
        $product->setPrice($productDto->price);
        $product->setDescription($productDto->description);
        $product->setQuantity($productDto->quantity);

        return $product;
    }
}