<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends BaseController
{
    #[Route (path: "/api/users/me", methods: "GET")]
    public function getCurrentUser(#[CurrentUser] User $user): JsonResponse
    {
        return $this->jsonUserRead($user);
    }
}