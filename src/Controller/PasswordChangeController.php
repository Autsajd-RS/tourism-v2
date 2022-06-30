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

    #[Route(path: '/profiles/password/forgot/request', methods: ['POST'])]
    public function requestToChangePassword(Request $request): JsonResponse
    {
        //first request to make new password
        //than email with link will be sent
        //than call newPassword api with verification code to complete forgot password process
        try {
            $this->profileService->forgotPasswordRequest(request: $request);
        } catch (\JsonException $e) {
        }

        return $this->json('ok', Response::HTTP_OK);
    }

    #[Route(path: '/profiles/password/new/{verificationCode}', methods: ['POST'])]
    public function newPassword(string $verificationCode, Request $request): JsonResponse
    {
        $response = $this->profileService->newPassword(
            verificationCode: $verificationCode,
            request: $request
        );

        if ($response instanceof ErrorResponse) {
            return $this->json($response, Response::HTTP_FORBIDDEN);
        }

        return $this->jsonUserRead($response);
    }
}