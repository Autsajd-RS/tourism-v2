<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\DestinationComment;
use App\Entity\User;
use App\Repository\DestinationCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class DestinationCommentService
{
    private const SEARCH_TERMS = [
        'destination',
        'user',
    ];

    public function __construct(
        private Crud $crud,
        private DestinationCommentRepository $commentRepository,
        private AuthorizationService $authorizationService,
    )
    {
    }

    public function create(Request $request, User $user): ErrorResponse|DestinationComment
    {
        /** @var DestinationComment $comment */
        $comment = $this->crud->createFromRequest(request: $request, entityClass: DestinationComment::class);

        $destination = $this->crud->extractDestinationFromRequest(request: $request);

        if ($destination instanceof ErrorResponse) {
            return $destination;
        }

        $comment
            ->setDestination($destination)
            ->setUser($user);

        $violations = $this->crud->validateEntity(entity: $comment);

        if (count($violations) > 0) {
            return new ErrorResponse(
                message: 'Invalid entity',
                errors: Crud::formatViolations($violations)
            );
        }

        $this->crud->create(entity: $comment);

        return $comment;
    }

    public function get(int $commentId): ?DestinationComment
    {
        return $this->commentRepository->find($commentId);
    }

    public function delete(int $commentId): void
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            return;
        }

        if ($this->authorizationService->authorizeComment(comment: $comment)) {
            $this->crud->remove(entity: $comment);
        }
    }

    public function search(Request $request): array
    {
        $criteria = [];

        $query = $request->query->all();

        foreach ($query as $item => $value) {
            if (!in_array($item, self::SEARCH_TERMS, true)) {
                continue;
            }

            $criteria[$item] = $value;
        }

        return $this->commentRepository->findBy(criteria: $criteria);
    }

    public function patch(int $id, Request $request)
    {
        $comment = $this->get(commentId: $id);

        if (!$comment) {
            return new ErrorResponse(
                message: 'Invalid entity',
                errors: ['comment' => 'not found']
            );
        }

        if (!$this->authorizationService->authorizeComment(comment: $comment)) {
            return new ErrorResponse(
                message: 'Access error',
                errors: ['comment' => 'not valid owner']
            );
        }

        try {
            $updateContext = $this->crud->normalizeRequestContent(request: $request);

            $comment = $this->crud->partialUpdate(
                entity: $comment,
                entityPatchGroup: DestinationComment::GROUP_PATCH,
                updateContext: $updateContext
            );

            $violations = $this->crud->validateEntity($comment);

            if (count($violations) > 0) {
                return new ErrorResponse(
                    message: 'Entity is not valid',
                    errors: Crud::formatViolations($violations)
                );
            }

            $this->crud->patch(entity: $comment);

            return $comment;

        } catch (\JsonException|ExceptionInterface $e) {
            return new ErrorResponse(message: 'Update failed', errors: ['server' => $e->getMessage()]);
        }
    }
}