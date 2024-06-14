<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use App\Repository\ProductRepository;
use App\Entity\Product;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProductList(ProductRepository $productRepository, 
                                    SerializerInterface $serializer,
                                    Request $request,
                                    TagAwareCacheInterface $cachePool): JsonResponse
    {
        // get the page and limit for pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getProductList-" . $page . '-' . $limit;
       
        //fetch all products
        $products = $cachePool->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {
            $item->tag("productsCache");
            $productList = $productRepository->findAllWithPagination($page, $limit);
            
            return $serializer->serialize($productList, 'json'); // serialize in json
        });
        
        return new JsonResponse($productList, Response::HTTP_OK, [], true); // return the productList with response 200
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
