<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Domain\ProductDomain;
use App\DataTransfertObject\ProductDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api')]
final class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product_index', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository
    ): ?JsonResponse
    {
        $products = $productRepository->findAll();

        return $this->json($products);
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(
        int $id,
        ProductDomain $productDomain
    ): JsonResponse
    {
        $productToShow = $productDomain->findProductById($id);
        return $this->json($productToShow);
    }

    #[Route('/product', name: 'app_product_create' , methods: ['POST'])]
    public function create(
        ProductDomain $productDomain,
        #[MapRequestPayload] ProductDto $productDto
    ): JsonResponse
    {
        $newProduct = $productDomain->register($productDto);
        return $this->json($newProduct, Response::HTTP_CREATED, []);
    }

    #[Route('/product/{id}', name: 'app_product_update', methods: ['PUT'])]
    public function update(
        int $id,
        ProductDomain $productDomain,
        #[MapRequestPayload] ProductDto $productDto
    ): JsonResponse
    {
        $productToUpdate = $productDomain->findProductById($id);

        $updatedProduct = $productDomain->updateProduct($productToUpdate, $productDto);

        return $this->json($updatedProduct, Response::HTTP_OK, []);
    }

    #[Route('/product/{id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        ProductDomain $productDomain,
    ): JsonResponse
    {
        $productToDelete = $productDomain->findProductById($id);
        $productDomain->removeProduct($productToDelete);

        return $this->json(['message' => 'Product deleted successfully']);
    }

}
