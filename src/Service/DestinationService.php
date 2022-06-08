<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Entity\DestinationComment;
use App\Repository\DestinationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DestinationService
{
    public function __construct(
        private DestinationRepository $destinationRepository,
        private Crud $crud,
    )
    {
    }

    /**
     * @return Destination[]
     */
    public function list(): array
    {
        return $this->destinationRepository->list();
    }

    public function findById(int $id): ?Destination
    {
        return $this->destinationRepository->find($id);
    }

    /**
     * @param Request $request
     * @return ErrorResponse|Destination[]
     */
    public function listByCategoryOrCity(Request $request): ErrorResponse|array
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'List failed', errors: ['server' => $e->getMessage()]);
        }

        $cityId = $params->cityId ?? null;
        $categoryId = $params->categoryId ?? null;

        return $this->destinationRepository->searchByCityAndCategory(cityId: $cityId, categoryId: $categoryId);
    }

    public function create(Request $request): ErrorResponse|Destination
    {
        $destination = $this->crud->deserializeEntity(request: $request, entityClass: Destination::class);

        if ($destination instanceof ErrorResponse) {
            return $destination;
        }

        if ($destination instanceof Destination) {
            $city = $this->crud->extractCityFromRequest(request: $request);
            $category = $this->crud->extractCategoryFromRequest(request: $request);

            if ($city instanceof ErrorResponse) {
                return $city;
            }

            if ($category instanceof ErrorResponse) {
                return $category;
            }

            $destination->setCity($city)->setCategory($category);
            
            $this->crud->create($destination);
            return $destination;
        }

        return new ErrorResponse(message: 'Something went wrong');
    }

    public function patch(int $destinationId, Request $request): ErrorResponse|Destination
    {
        $destination = $this->destinationRepository->find($destinationId);

        if (!$destination) {
            return new ErrorResponse(message: 'Destination Edit failed', errors: ['destination' => 'not found']);
        }

        try {
            $updateContext = $this->crud->normalizeRequestContent(request: $request);

            $destination = $this->crud->partialUpdate(
                entity: $destination,
                entityPatchGroup: Destination::GROUP_PATCH,
                updateContext: $updateContext,
                excludedProperties: ['city', 'category']
            );

            $violations = $this->crud->validateEntity($destination);

            if (count($violations) > 0) {
                return new ErrorResponse(
                    message: 'Entity is not valid',
                    errors: Crud::formatViolations($violations)
                );
            }

            $category = $this->crud->extractCategoryFromRequest(request: $request);

            if (!$category instanceof ErrorResponse) {
                $destination->setCategory($category);
            }

            $this->crud->patch(entity: $destination);

            return $destination;

        } catch (ExceptionInterface|\JsonException $e) {
            return new ErrorResponse(message: 'Update failed', errors: ['server' => $e->getMessage()]);
        }
    }
}