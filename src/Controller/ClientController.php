<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Client;

class ClientController extends AbstractController
{
    #[Route('/api/clients/{id}', name: 'client', methods: ['GET'])]
    public function getOneClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($client, 'json', ['groups' => 'getClient']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
