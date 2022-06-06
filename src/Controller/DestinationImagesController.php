<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Service\DestinationImageService;
use App\Service\DestinationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DestinationImagesController extends  BaseController
{
    public function __construct(
        private DestinationImageService $destinationImageService,
        private DestinationService $destinationService
    )
    {
    }

    #[Route(path: '/admin/destinations/{id}/images/primary', methods: ['POST'])]
    public function primary(int $id, Request $request): JsonResponse
    {
        $destination = $this->destinationService->findById($id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Primary image upload failed',
                errors: ['destination' => 'not found']
            ), Response::HTTP_NOT_FOUND);
        }

        $destination = $this->destinationImageService->addFromRequest(request: $request, destination: $destination);

        if ($destination instanceof ErrorResponse) {
            return $this->json($destination, Response::HTTP_NOT_FOUND);
        }

        return $this->jsonDestinationRead(destination: $destination, status: Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/admin/destinations/{id}/images/{imageId}', methods: ['DELETE'])]
    public function deleteImage(int $id, int $imageId): JsonResponse
    {
        $destination = $this->destinationService->findById($id);

        if (!$destination) {
            return $this->json(new ErrorResponse(
                message: 'Image delete failed',
                errors: ['destination' => 'not found']
            ), Response::HTTP_NOT_FOUND);
        }

        $this->destinationImageService->deleteImage(imageId: $imageId);

        return $this->jsonDestinationRead(destination: $destination, status: Response::HTTP_ACCEPTED);
    }
}