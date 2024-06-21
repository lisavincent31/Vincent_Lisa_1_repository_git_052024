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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use App\Entity\Client;
use App\Entity\User;

class ClientController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des utilisateurs liés à un client connecté
     * 
     * @OA\Response(
     *      response=200,
     *      description="Retourne l'ensemble des utilisateurs liés à un client connecté",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
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
     * @OA\Tag(name="Users")
     * 
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Cette méthode permet de récupérer le détail d'un utilisateur lié à un client connecté
     * 
     * @OA\Response(
     *      response=200,
     *      description="Retourne le détail d'un utilisateur lié à un client connecté",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class))
     *      )
     * )
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */

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

    /**
     * Cette méthode permet de créer un nouvel utilisateur lié à un client connecté
     * 
     * @OA\Response(
     *      response=200,
     *      description="Création d'un utilisateur lié à un client connecté",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *      )
     * )
     * @OA\RequestBody(
     *      request="createUser",
     *      description="Les informations de l'utilisateur",
     *      required=true,
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="firstname", type="property_type"),
     *          @OA\Property(property="lastname", type="property_type"),
     *          @OA\Property(property="email", type="property_type"),
     *      )
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/users', name:'create_user', methods: ['POST'])]
    public function createUser(Request $request,
                                EntityManagerInterface $manager,
                                SerializerInterface $serializer,
                                UrlGeneratorInterface $urlGenerator, 
                                ValidatorInterface $validator,
                                TagAwareCacheInterface $cachePool): JsonResponse
    {
        try {
            $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
        // $user = $serializer->deserialize($request->getcontent(), User::class, 'json');
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

    /**
     * Cette méthode permet de supprimer un utilisateur lié à un client conencté
     * 
     * @OA\Response(
     *      response=200,
     *      description="Suppression d'un utilisateur lié à un client connecté",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=User::class))
     *      )
     * )
     * @OA\Tag(name="Users")
     * 
     * @param User $user
     * @return JsonResponse
     */
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
