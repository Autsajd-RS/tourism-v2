<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\City;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserProfileService
{
    public function __construct(
        private Security $security,
        private Crud $crud
    )
    {
    }

    public function currentUser(): UserInterface
    {
        return $this->security->getUser();
    }

    public function patch(Request $request): ErrorResponse|User
    {
        /** @var User $user */
        $user = $this->currentUser();

        try {
            $updateContext = $this->crud->normalizeRequestContent(request: $request);

            $user = $this->crud->partialUpdate(
                entity: $user,
                entityPatchGroup: User::GROUP_PATCH,
                updateContext: $updateContext,

            );

            $violations = $this->crud->validateEntity(entity: $user);

            if (count($violations) > 0) {
                return new ErrorResponse(
                    message: 'Entity is not valid',
                    errors: Crud::formatViolations($violations)
                );
            }

            $city = $this->crud->extractCityFromRequest(request: $request);

            if ($city instanceof City) {
                $user->setCity($city);
            }

            $this->crud->patch(entity: $user);

            return $user;

        } catch (\JsonException|ExceptionInterface $e) {
            return new ErrorResponse(message: 'Update failed', errors: ['server' => $e->getMessage()]);
        }
    }

    public function appendProfilePhoto(User $user, string $filename): void
    {
        $user->setAvatar($filename);

        $this->crud->patch($user);
    }
}