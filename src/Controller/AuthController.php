<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\User;
use App\Service\AuthenticationService;
use App\Service\ListService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends BaseController
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private ListService $listService
    )
    {
    }

    #[Route(path: '/api/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $user = $this->authenticationService->userFromRequest(request: $request, groups: [User::GROUP_REGISTER]);

        if (!$user) {
            return $this->json(new ErrorResponse(
                message: 'Server Error',
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = $this->authenticationService->bindCity(request: $request, user: $user);

        $violations = $this->authenticationService->validateUser(user: $user, groups: [User::GROUP_REGISTER]);

        if (count($violations) > 0) {
            return $this->json(new ErrorResponse(
                message: 'Registration Error',
                errors: AuthenticationService::formatViolations($violations)
            ), Response::HTTP_BAD_REQUEST);
        }

        $user = $this->authenticationService->registerUser($user);

        $this->listService->prependUserLists(user: $user);

        return $this->jsonUserRead($user);
    }

    #[Route(path: '/api/verify/{verificationCode}', methods: ['GET'])]
    public function verify(string $verificationCode): JsonResponse
    {
        $response = $this->authenticationService->verifyUser(verificationCode: $verificationCode);

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->jsonUserRead($response);
    }
}