<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Entity\Client;
use App\Entity\User;

class ClientController extends AbstractController
{
    #[Route('/api/clients/{id}', name: 'client', methods: ['GET'])]
    public function getOneClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($client, 'json', ['groups' => 'getClient']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}/users', name: 'users', methods: ['GET'])]
    public function getUsers(SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();
        $users = $client->getUsers();

        $json = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{client_id}/users/{id}', name: 'detail_user', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();
        if($user->getClient() == $client) {
            $json = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
            return new JsonResponse($json, Response::HTTP_OK, [], true);
        }
    }

    #[Route('/api/clients/{client_id}/users', name:'create_user', methods: ['POST'])]
    public function createUser(Request $request,
                                EntityManagerInterface $manager,
                                SerializerInterface $serializer,
                                UrlGeneratorInterface $urlGenerator, 
                                ValidatorInterface $validator): JsonResponse
    {
        $user = $serializer->deserialize($request->getcontent(), User::class, 'json');
        $user->setClient($this->getUser());

        $errors = $validator->validate($user);
        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($user);
        $manager->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        $location = $urlGenerator->generate('detail_user', ['client_id' => $this->getUser()->getId(), 'id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/clients/{client_id}/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $manager): JsonResponse
    {
        $client = $this->getUser();

        if($user->getClient() == $client) {
            $manager->remove($user);
            $manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
}
