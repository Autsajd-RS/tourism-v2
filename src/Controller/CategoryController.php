<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService
    )
    {
    }

    #[Route(path: '/api/categories', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->jsonCityRead($this->categoryService->list());
    }

    #[Route(path: '/admin/categories', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $category = $this->categoryService->create(request: $request);

        if ($category instanceof ErrorResponse) {
            return $this->json($category, Response::HTTP_BAD_REQUEST);
        }

        return $this->jsonCategoryRead($category, Response::HTTP_CREATED);
    }

    #[Route(path: '/admin/categories/{id}', methods: ['PATCH'])]
    public function patch(int $id, Request $request): JsonResponse
    {
        $category = $this->categoryService->patch(categoryId: $id, request: $request);

        if ($category instanceof ErrorResponse) {
            return $this->json($category, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->jsonCategoryRead($category, Response::HTTP_ACCEPTED);
    }

}