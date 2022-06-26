<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\LocationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends BaseController
{
    public function __construct(
        private LocationService $locationService
    )
    {
    }

    #[Route (path: "/api/users/me", methods: "GET")]
    public function getCurrentUser(#[CurrentUser] User $user): JsonResponse
    {
        return $this->jsonUserRead($user);
    }

    #[Route(path: '/api/users/locations', methods: 'POST')]
    public function logMyLocation(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $this->locationService->logLocation(user: $user, request: $request);

        return $this->json('ok', Response::HTTP_CREATED);
    }

    #[Route(path: '/api/users/locations', methods: 'GET')]
    public function myLocations(#[CurrentUser] User $user): JsonResponse
    {
        $locations = $this->locationService->getUserLocations(user: $user);

        return $this->json($locations);
    }
}