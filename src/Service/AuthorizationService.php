<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\DTO\PasswordChangeCredentials;
use App\Entity\DestinationComment;
use App\Entity\User;
use App\Entity\WishList;
use Symfony\Component\Security\Core\Security;

class AuthorizationService
{
    public function __construct(
        private Security $security
    )
    {
    }

    private function getUser(): User|null
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return null;
        }

        return $user;
    }

    public function authorizeComment(DestinationComment $comment): bool
    {
        return $comment->getUser()?->getId() === $this->getUser()?->getId();
    }

    public function authorizeList(WishList $list): bool
    {
        return $list->getUser()?->getId() === $this->getUser()?->getId();
    }

    public static function authorizePasswordChangeCredentials(
        PasswordChangeCredentials $credentials,
        User $user
    ): ErrorResponse|bool
    {
        if (!password_verify($credentials->getCurrentPassword(), $user->getPassword())) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: ['currentPassword' => 'Uneli ste pogrešnu lozinku']
            );
        }

        if ($credentials->getNewPassword() !== $credentials->getRepeatedPassword()) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: ['repeatedPassword' => 'Lozinke se ne poklapaju']
            );
        }

        return true;
    }

    public static function authorizeForgotPassword(User $user, string $verificationCode): ErrorResponse|bool
    {
        if ($user->getForgotPasswordVerificationToken() !== $verificationCode) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: ['verificationCode' => 'Nepravlian verifikacioni kod']
            );
        }

        if ($user->getForgotPasswordTokenExpire() < (new \DateTime())) {
            return new ErrorResponse(
                message: 'Invalid credentials',
                errors: ['verificationCode' => 'Verifikcationi kod je istekao']
            );
        }

        return true;
    }
}