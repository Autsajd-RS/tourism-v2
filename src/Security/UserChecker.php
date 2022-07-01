<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public const USER_NOT_VERIFIED = 'Korisnik nije verifikovan';

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(message: self::USER_NOT_VERIFIED);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // TODO: Implement checkPostAuth() method.
    }
}