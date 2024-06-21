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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use App\Repository\ProductRepository;
use App\Entity\Product;

class ProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des téléphones de luxes référencés dans notre API
     * 
     * @OA\Response(
     *      response=200,
     *      description="Retourne la liste des téléphones",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Product::class))
     *      )
     * )
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Le numéro de la page que l'on souhaite récupérer.",
     *      @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *      name="limit",
     *      in="query",
     *      description="Le nombre d'éléments que l'on veut récupérer",
     *      @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Products")
     * 
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
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
        
        return new JsonResponse($products, Response::HTTP_OK, [], true); // return the productList with response 200
    }

    /**
     * Cette méthode permet de récupérer le détail d'un seul téléphone de luxe référencé dans notre API
     * 
     * @OA\Response(
     *      response=200,
     *      description="Retourne le détail d'un téléphone",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Product::class))
     *      )
     * )
     * @OA\Tag(name="Products")
     * 
     * @param Product $product
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function getOneProduct(Product $product, SerializerInterface $serializer): JsonResponse
    {
        // fetch one specific book and serialize in json
        $jsonProduct = $serializer->serialize($product, 'json');
        // return the book with response status 200
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
