<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\DTO\VerificationCheckerData;
use App\Entity\City;
use App\Entity\User;
use App\Message\ForgotPasswordRequested;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserProfileService
{
    public function __construct(
        private Security $security,
        private Crud $crud,
        private UserPasswordHasherInterface $hasher,
        private MessageBusInterface $bus
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

            $violations = $this->crud->validateEntity(entity: $user, group: User::GROUP_PATCH);

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

    public function forgotPasswordRequest(User $user): User
    {
        $verificationCheckerData = new VerificationCheckerData();

        $user
            ->setForgotPasswordVerificationToken($verificationCheckerData->getCode())
            ->setForgotPasswordTokenExpire($verificationCheckerData->getExpireAt());

        $this->crud->patch(entity: $user);

        $this->bus->dispatch(new ForgotPasswordRequested(
            email: $user->getEmail(),
            verificationCode: $user->getForgotPasswordVerificationToken()
        ));

        return $user;
    }

    public function newPassword(User $user, string $verificationCode, Request $request): ErrorResponse|User
    {
        $shouldMakeNewPassword = AuthorizationService::authorizeForgotPassword(
            user: $user,
            verificationCode: $verificationCode
        );

        if ($shouldMakeNewPassword instanceof ErrorResponse) {
            return $shouldMakeNewPassword;
        }

        if ($shouldMakeNewPassword !== true) {
            return new ErrorResponse(
                message: 'Server error',
                errors: ['server' => 'something went wrong']
            );
        }

        $credentials = $this->crud->extractForgotPasswordCredentialsFromRequest(request: $request);

        if ($credentials instanceof ErrorResponse) {
            return $credentials;
        }

        $user
            ->setPassword($this->hasher->hashPassword(user: $user, plainPassword: $credentials->getNewPassword()))
            ->setForgotPasswordVerificationToken(null)
            ->setForgotPasswordTokenExpire(null);

        $this->crud->patch(entity: $user);

        $credentials->eraseCredentials();

        return $user;
    }

    public function changePassword(User $user, Request $request): ErrorResponse|User
    {
        $credentials = $this->crud->extractChangePasswordCredentialsFromRequest(request: $request);

        if ($credentials instanceof ErrorResponse) {
            return $credentials;
        }

        $shouldChangePassword = AuthorizationService::authorizePasswordChangeCredentials(
            credentials: $credentials,
            user: $user
        );

        if ($shouldChangePassword instanceof ErrorResponse) {
            return $shouldChangePassword;
        }

        $user->setPassword($this->hasher->hashPassword($user, $credentials->getNewPassword()));

        $credentials->eraseCredentials();

        $this->crud->patch(entity: $user);

        return $user;
    }
}