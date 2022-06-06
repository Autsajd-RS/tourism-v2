<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private Crud $crud
    )
    {
    }

    /**
     * @return Category[]
     */
    public function list(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function create(Request $request): ErrorResponse|Category
    {
        $category = $this->crud->deserializeEntity(request: $request, entityClass: Category::class);

        if ($category instanceof ErrorResponse) {
            return $category;
        }

        if ($category instanceof Category) {
            $this->crud->create($category);

            return $category;
        }

        return new ErrorResponse(message: 'Something went wrong');
    }

    public function patch(int $categoryId, Request $request): ErrorResponse|Category
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            return new ErrorResponse(message: 'Category Edit failed', errors: ['category' => 'not found']);
        }

        try {
            $updateContext = $this->crud->normalizeRequestContent(request: $request);

            $category = $this->crud->partialUpdate(
                entity: $category,
                entityPatchGroup: Category::GROUP_PATCH,
                updateContext: $updateContext
            );

            $violations = $this->crud->validateEntity($category);

            if (count($violations) > 0) {
                return new ErrorResponse(
                    message: 'Entity is not valid',
                    errors: Crud::formatViolations($violations)
                );
            }

            $this->crud->patch($category);

            return $category;

        } catch (\JsonException|ExceptionInterface $e) {
            return new ErrorResponse(message: 'Update failed', errors: ['server' => $e->getMessage()]);
        }
    }
}