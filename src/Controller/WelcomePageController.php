<?php

namespace App\Controller;

use App\Service\WelcomePageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WelcomePageController extends BaseController
{
    public function __construct(
        private WelcomePageService $welcomePageService
    )
    {
    }

    #[Route(path: '/welcome', methods: ['GET'])]
    public function welcome(): JsonResponse
    {
        return $this->json($this->welcomePageService->statistics());
    }
}