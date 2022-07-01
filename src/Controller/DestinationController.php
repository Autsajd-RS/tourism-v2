<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Entity\User;
use App\Service\DestinationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DestinationController extends BaseController
{
    public function __construct(
        private DestinationService $destinationService
    )
    {
    }

    #[Route(path: '/api/destinations/{id}', methods: ['GET'])]
    public function one(int $id): JsonResponse
    {
        $destination = $this->destinationService->findById(id: $id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Fetch failed',
                errors: ['destination', 'not found']
            ));
        }

        $this->destinationService->incrementAttendance(destination: $destination);

        return $this->jsonDestinationRead(destination: $destination);
    }

    #[Route(path: '/api/destinations', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->jsonDestinationRead($this->destinationService->list());
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/api/destinations/by', methods: ['POST'])]
    public function listBy(Request $request): JsonResponse
    {
        $response = $this->destinationService->listByCriteria(request: $request);

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($response);
    }

    #[Route(path: '/admin/destinations', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $destination = $this->destinationService->create(request: $request);

        if ($destination instanceof ErrorResponse) {
            return $this->json($destination, Response::HTTP_BAD_REQUEST);
        }

        return $this->jsonDestinationRead($destination, Response::HTTP_CREATED);
    }

    #[Route(path: '/admin/destinations/{id}', methods: ['PATCH'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $destination = $this->destinationService->patch(destinationId: $id, request: $request);

        if ($destination instanceof ErrorResponse) {
            return $this->json($destination, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->jsonDestinationRead($destination, Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/api/destinations/radius', methods: ['POST'])]
    public function destinationsInRadius(Request $request): JsonResponse
    {
        $result = $this->destinationService->findByCoordinates(request: $request);

        if ($result instanceof ErrorResponse) {
            return $this->json($result, Response::HTTP_CONFLICT);
        }

        return $this->json(array_values($result));
    }

    #[Route(path: '/api/destinations/{id}/like', methods: ['GET'])]
    public function likeDestination(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $destination = $this->destinationService->findById(id: $id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Fetch failed',
                errors: ['destination', 'not found']
            ));
        }

        $like = $this->destinationService->addLike(destination: $destination, user: $user);

        return $this->json($destination, Response::HTTP_CREATED);
    }

    #[Route(path: '/api/destinations/{id}/unlike', methods: ['GET'])]
    public function unlikeDestination(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $destination = $this->destinationService->findById(id: $id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Fetch failed',
                errors: ['destination', 'not found']
            ));
        }

        $this->destinationService->undoLike(
            destination: $destination,
            user: $user
        );

        return $this->jsonDestinationRead(destination: $destination, status: Response::HTTP_CREATED);
    }

    #[Route(path: '/api/destinations/{id}/likes-list')]
    public function listOfLikes(int $id): JsonResponse
    {
        $destination = $this->destinationService->findById(id: $id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Fetch failed',
                errors: ['destination', 'not found']
            ));
        }

        $list = [
            'likes' => $this->destinationService->listOfLikesOrDislikes(destination: $destination),
            'dislikes' => $this->destinationService->listOfLikesOrDislikes(destination: $destination, likes: false)
        ];

        return $this->json($list);
    }
}