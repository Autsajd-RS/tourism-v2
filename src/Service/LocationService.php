<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLocation;
use App\Repository\UserLocationRepository;
use Symfony\Component\HttpFoundation\Request;

class LocationService
{
    public function __construct(
        private UserLocationRepository $userLocationRepository,
        private Crud $crud
    )
    {
    }

    public function logLocation(User $user, Request $request): void
    {
        $location = $this->crud->deserializeEntity(request: $request, entityClass: UserLocation::class);

        if ($location instanceof UserLocation) {
            $location
                ->setUserId($user->getId())
                ->setCreatedAt(new \DateTime());

            /** @var UserLocation[] $similar */
            $similar = $this->userLocationRepository->findSimilar(location: $location);

            if (count($similar) > 0) {
                $similar[0]->setTimes($similar[0]->getTimes() + 1);
                $this->crud->patch($similar[0]);
                return;
            }

            $this->crud->create(entity: $location);
        }
    }

    public function getUserLocations(User $user): array
    {
        return $this->userLocationRepository->findBy(['userId' => $user->getId()]);
    }
}