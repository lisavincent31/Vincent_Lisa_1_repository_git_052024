<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Repository\UserRepository;

use App\Entity\Client;
use App\Entity\User;

class ClientController extends AbstractController
{
    #[Route('/api/clients/{client_id}', name: 'client', methods: ['GET'])]
    public function getOneClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $json = $serializer->serialize($client, 'json', $context);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getUsers(SerializerInterface $serializer,
                            Request $request,
                            TagAwareCacheInterface $cachePool,
                            UserRepository $userRepository): JsonResponse
    {
        $client = $this->getUser();

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $idCache = "getUserList-" . $client->getId() . $page . "-" . $limit;

        $userList = $cachePool->get($idCache, function(ItemInterface $item) use ($userRepository, $page, $limit, $serializer, $client) {
            $item->tag("usersCache-" . $client->getId());
            $context = SerializationContext::create()->setGroups(['getUsers']);
            $users = $userRepository->findAllByClientWithPagination($client, $page, $limit);
            return $serializer->serialize($users, 'json', $context);
        });
        
        return new JsonResponse($userList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detail_user', methods: ['GET'])]
    public function getUserDetail(User $user, SerializerInterface $serializer): JsonResponse
    {
        $client = $this->getUser();
        if($user->getClient() == $client) {
            $context = SerializationContext::create()->setGroups(['getUsers']);
            $json = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($json, Response::HTTP_OK, [], true);
        }
    }

    #[Route('/api/users', name:'create_user', methods: ['POST'])]
    public function createUser(Request $request,
                                EntityManagerInterface $manager,
                                SerializerInterface $serializer,
                                UrlGeneratorInterface $urlGenerator, 
                                ValidatorInterface $validator,
                                TagAwareCacheInterface $cachePool): JsonResponse
    {
        $user = $serializer->deserialize($request->getcontent(), User::class, 'json');
        $user->setClient($this->getUser());

        $errors = $validator->validate($user);
        if($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($user);
        $manager->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $json = $serializer->serialize($user, 'json', $context);
        $location = $urlGenerator->generate('detail_user', ['client_id' => $this->getUser()->getId(), 'id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $cachePool->invalidateTags(["usersCache"]);
        
        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, 
                            EntityManagerInterface $manager,
                            TagAwareCacheInterface $cachePool): JsonResponse
    {
        $client = $this->getUser();

        if($user->getClient() == $client) {
            $manager->remove($user);
            $manager->flush();

            $cachePool->invalidateTags(["usersCache-" . $client->getId()]);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }else{
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }
    }
}
