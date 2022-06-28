<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Entity\DestinationLike;
use App\Entity\User;
use App\Repository\DestinationLikeRepository;
use App\Repository\DestinationRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DestinationService
{
    public function __construct(
        private DestinationRepository $destinationRepository,
        private Crud $crud,
        private DestinationLikeRepository $likeRepository,
        private Security $security
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
     * @throws \Exception
     */
    public function listByCriteria(Request $request): ErrorResponse|array
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'List failed', errors: ['server' => $e->getMessage()]);
        }

        $criteria = [];
        $criteria['cityId'] = $params->cityId ?? null;
        $criteria['categoryId'] = $params->categoryId ?? null;
        $criteria['name'] = $params->name ?? null;
        $criteria['sort'] = $params->sort ?? null;
        $criteria['limit'] = $params->limit ?? null;
        $criteria['page'] = $params->page ?? null;
        $criteria['nearMe'] = $params->nearMe ?? null;

        if ($criteria['nearMe'] === true) {
            /** @var User $user */
            $user = $this->security->getUser();

            if ($user && $user->getCity()) {
                $criteria['cityId'] = $user->getCity()->getId();
            }
        }

        unset($criteria['nearMe']);

        return $this->destinationRepository->searchByCriteria(criteria: $criteria);
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

    public function findByCoordinates(Request $request): ErrorResponse|array
    {
        try {
            $params = json_decode((string)$request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new ErrorResponse(message: 'Request resolve error', errors: ['request' => 'could not decode request']);
        }

        if (!isset($params->latitude, $params->longitude)) {
            return new ErrorResponse(message: 'Request error', errors: ['request' => 'bad request']);
        }


        $sql = 'SELECT id, name, latitude, longitude FROM destination';

        try {
            $result = $this->crud->getEntityManager()->getConnection()->fetchAllAssociative($sql);
        } catch (Exception $e) {
            return new ErrorResponse(message: 'Fetch destinations error', errors: ['destinations' => 'fetch error']);
        }

        $meter = 1000;
        $R = 6371e3;
        $lng = $params->longitude;
        $lat = $params->latitude;

        return array_filter($result,static function ($destination) use ($meter, $R, $lng, $lat) {
            $f1 = $lat * (M_PI /180);
            $f2 = $lng * (M_PI /180);
            $deltaF = ((float)$destination['latitude'] - $lat) * M_PI / 180;
            $deltaX = ((float)$destination['longitude'] - $lng) * M_PI / 180;
            $a = sin($deltaF/2) ** 2 + cos($f1) * cos($f2) * sin($deltaX/2) ** 2;
            $c = 2 * (atan2(sqrt($a), sqrt(1-$a)));

            $d = $R * $c;
            return $meter >= $d;
        });
    }

    public function addLike(Destination $destination, User $user): DestinationLike
    {
        /** @var DestinationLike[] $previousLike */
        $previousLike = $this->likeRepository->isLikedByUser(destination: $destination, user: $user);
        /** @var DestinationLike[] $previousDislike */
        $previousDislike = $this->likeRepository->isDislikedByUser(destination: $destination, user: $user);

        if (count($previousDislike)) {
            $previousDislike[0]->setDeleted(true);
            $this->crud->patch($previousDislike[0]);
        }

        if (count($previousLike)) {
            if ($previousLike[0]->isDeleted()) {
                $previousLike[0]->setDeleted(false);
            } else {
                $previousLike[0]->setDeleted(true);
            }

            $this->crud->patch($previousLike[0]);
            return $previousLike[0];
        }

        $destination->setPopularity($destination->getPopularity() + 1);
        $this->crud->patch(entity: $destination);

        $like = (new DestinationLike())
            ->setDestinationId($destination->getId())
            ->setUserId($user->getId())
            ->setCreatedAt(new \DateTime())
            ->setDeleted(false)
            ->setNegative(false);

        $this->crud->create(entity: $like);

        return $like;
    }

    public function undoLike(Destination $destination, User $user): DestinationLike
    {
        /** @var DestinationLike[] $previousLike */
        $previousLike = $this->likeRepository->isLikedByUser(destination: $destination, user: $user);
        /** @var DestinationLike[] $previousDislike */
        $previousDislike = $this->likeRepository->isDislikedByUser(destination: $destination, user: $user);

        if (count($previousLike)) {
            $previousLike[0]->setDeleted(true);
            $this->crud->patch(entity: $previousLike);
        }

        if (count($previousDislike)) {
            if ($previousDislike[0]->isDeleted()) {
                $previousDislike[0]->setDeleted(false);
            } else {
                $previousDislike[0]->setDeleted(true);
            }

            $this->crud->patch($previousDislike[0]);
            return $previousDislike[0];
        }

        $like = (new DestinationLike())
            ->setDestinationId($destination->getId())
            ->setUserId($user->getId())
            ->setCreatedAt(new \DateTime())
            ->setDeleted(false)
            ->setNegative(true);

        $destination->setPopularity($destination->getPopularity() - 1);

        $this->crud->create(entity: $like);
        $this->crud->patch(entity: $destination);

        return $like;
    }

    public function incrementAttendance(Destination $destination): void
    {
        $destination->setAttendance($destination->getAttendance() + 1);
        $this->crud->patch(entity: $destination);
    }
}