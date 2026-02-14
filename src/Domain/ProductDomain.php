<?php

namespace App\Domain;

use App\Entity\Product;
use App\DataTransfertObject\ProductDto;
use App\Mapper\ProductMapper;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDomain 
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository
    ){}

    public function register(
        ProductDto $productDto,
    ): Product
    {
        $product = ProductMapper::fromDto($productDto);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function findProductById(
        int $id,
    ): Product
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        return $product;
    }

    public function removeProduct(Product $productToDelete): void
    {
        $this->entityManager->remove($productToDelete);
        $this->entityManager->flush();
    }

    public function updateProduct(Product $productToUpdate, ProductDto $productDto): Product
    {
        $productToUpdate->setName($productDto->name);
        $productToUpdate->setDescription($productDto->description);
        $productToUpdate->setPrice($productDto->price);
        $productToUpdate->setQuantity($productDto->quantity);
        $productToUpdate->setImageUrl($productDto->imageUrl);

        $this->entityManager->flush();

        return $productToUpdate;
    }

}