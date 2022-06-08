<?php

namespace App\Service;

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
}