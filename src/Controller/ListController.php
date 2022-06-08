<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\User;
use App\Service\AuthorizationService;
use App\Service\DestinationService;
use App\Service\ListService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListController extends BaseController
{
    public function __construct(
        private ListService $listService,
        private DestinationService $destinationService,
        private AuthorizationService $authorizationService
    )
    {
    }

    #[Route(path: '/api/lists/my', methods: ['GET'])]
    public function myLists(#[CurrentUser] User $user): JsonResponse
    {
        return $this->jsonListRead($user->getWishLists()->toArray());
    }

    #[Route(path: '/api/lists/{id}', methods: ['GET'])]
    public function one(int $id): JsonResponse
    {
        $list = $this->listService->getById(id: $id);

        if (!$list) {
            return $this->json(new ErrorResponse(
                message: 'Fetch failed',
                errors: ['list' => 'not found']
            ), Response::HTTP_NOT_FOUND);
        }

        if (!$this->authorizationService->authorizeList(list: $list)) {
            return $this->json(new ErrorResponse(
                message: 'Access error',
                errors: ['list' => 'not valid owner']
            ), Response::HTTP_FORBIDDEN);
        }

        return $this->jsonListRead(wishList: $list);
    }

    #[Route(path: '/api/lists', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $list = $this->listService->create(request: $request, user: $user);

        if ($list instanceof ErrorResponse) {
            return $this->json($list, Response::HTTP_BAD_REQUEST);
        }

        return $this->jsonListRead(wishList: $list, status: Response::HTTP_CREATED);
    }

    #[Route(path: '/api/lists/destination', methods: ['POST'])]
    public function addDestination(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $list = $this->listService->appendDestination(request: $request, user: $user);

        if ($list instanceof ErrorResponse) {
            return $this->json($list, Response::HTTP_BAD_REQUEST);
        }

        return $this->jsonListRead(wishList: $list, status: Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/api/lists/{listId}/destination/{destinationId}', methods: ['DELETE'])]
    public function removeDestination(int $listId, int $destinationId): JsonResponse
    {
        $list = $this->listService->getById(id: $listId);

        if (!$list) {
            return $this->json(new ErrorResponse(message: 'Delete failed', errors: ['list' => 'not found']), Response::HTTP_NOT_FOUND);
        }

        $destination = $this->destinationService->findById(id: $destinationId);

        if (!$destination) {
            return $this->json(new ErrorResponse(message: 'Delete failed', errors: ['destination' => 'not found']), Response::HTTP_NOT_FOUND);
        }

        $this->listService->removeDestination(list: $list, destination: $destination);

        return $this->jsonListRead(wishList: $list, status: Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/api/lists/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $list = $this->listService->getById(id: $id);

        if (!$list) {
            return $this->json(new ErrorResponse(message: 'Delete failed', errors: ['list' => 'not found']), Response::HTTP_NOT_FOUND);
        }

        $this->listService->delete(list: $list);

        //jbg
        return $this->json(new ErrorResponse(message: 'Deleted'), Response::HTTP_OK);
    }
}