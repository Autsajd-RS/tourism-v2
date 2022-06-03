<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected function jsonUserRead(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => User::GROUP_READ]);
    }
}