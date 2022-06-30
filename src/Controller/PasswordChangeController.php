<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\User;
use App\Service\UserProfileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PasswordChangeController extends BaseController
{
    public function __construct(
        private UserProfileService $profileService
    )
    {
    }

    #[Route(path: '/api/profiles/password/change', methods: ['POST'])]
    public function changePassword(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $response = $this->profileService->changePassword(
            user: $user,
            request: $request
        );

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_FORBIDDEN);
        }

        return $this->jsonUserRead($response);
    }

    #[Route(path: '/api/profiles/password/forgot/request', methods: ['GET'])]
    public function requestToChangePassword(#[CurrentUser] User $user): JsonResponse
    {
        //first request to make new password
        //than email with link will be sent
        //than call newPassword api with verification code to complete forgot password process
        return $this->jsonUserRead($this->profileService->forgotPasswordRequest(user: $user));
    }

    #[Route(path: '/api/profiles/password/new/{verificationCode}', methods: ['POST'])]
    public function newPassword(string $verificationCode, #[CurrentUser] User $user, Request $request): JsonResponse
    {
        $response = $this->profileService->newPassword(
            user: $user,
            verificationCode: $verificationCode,
            request: $request
        );

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_FORBIDDEN);
        }

        return $this->jsonUserRead($response);
    }
}