<?php

namespace App\Service;

use App\Repository\CityRepository;
use App\Repository\DestinationRepository;
use App\Repository\UserRepository;

class WelcomePageService
{
    public function __construct(
        private DestinationRepository $destinationRepository,
        private UserRepository $userRepository,
        private CityRepository $cityRepository
    )
    {
    }

    public function statistics(): array
    {
        return [
            'destinationCount' => $this->destinationRepository->findCount(),
            'userCount' => $this->userRepository->findCount(),
            'cities' => $this->cityRepository->top3(),
            'popularity' => $this->destinationRepository->findTopPopular(),
            'attendance' => $this->destinationRepository->findTopAttended()
        ];
    }
}