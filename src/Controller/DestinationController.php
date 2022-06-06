<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Service\DestinationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DestinationController extends BaseController
{
    public function __construct(
        private DestinationService $destinationService
    )
    {
    }

    #[Route(path: '/destinations', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->jsonDestinationRead($this->destinationService->list());
    }

    #[Route(path: '/destinations/by', methods: ['POST'])]
    public function listBy(Request $request): JsonResponse
    {
        $response = $this->destinationService->listByCategoryOrCity($request);

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->jsonDestinationRead($response);
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
}