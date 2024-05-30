<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\ProductRepository;
use App\Entity\Product;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProductList(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        //fetch all products
        $products = $productRepository->findAll();
        // serialize in json
        $productList = $serializer->serialize($products, 'json');
        // return the productList with response 200
        return new JsonResponse($productList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getOneProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        // fetch one specific book and serialize in json
        $jsonProduct = $serializer->serialize($product, 'json');
        // return the book with response status 200
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
