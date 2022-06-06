<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\User;
use App\Service\DigitalOceanSpacesService;
use App\Service\UserProfileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserProfileController extends BaseController
{
    public function __construct(
        private UserProfileService $profileService,
        private DigitalOceanSpacesService $digitalOceanSpacesService
    )
    {
    }

    #[Route(path: '/api/profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        /** @var User $user */
        $user = $this->profileService->currentUser();

        return $this->jsonUserRead($user);
    }

    #[Route(path: '/api/profile', methods: ['PATCH'])]
    public function edit(Request $request): JsonResponse
    {
        $user = $this->profileService->patch(request: $request);

        if ($user instanceof ErrorResponse) {
            return $this->json($user, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->jsonUserRead($user, Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/api/profile/photo', methods: ['POST'])]
    public function uploadPhoto(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        if (!$request->files->has('profilePhoto')) {
            return $this->json(new ErrorResponse(
                message: 'Photo upload failed',
                errors: ['photo' => 'not found']
            ), Response::HTTP_NOT_ACCEPTABLE);
        }

        $photo = $request->files->get('profilePhoto');

        $filename = $this->digitalOceanSpacesService->upload(
            uploadedFile: $photo,
            fileType: DigitalOceanSpacesService::PROFILE_IMAGE_TYPE
        );

        $this->profileService->appendProfilePhoto(user: $user, filename: $filename);

        return $this->jsonUserRead(user: $user, status: Response::HTTP_ACCEPTED);
    }
}