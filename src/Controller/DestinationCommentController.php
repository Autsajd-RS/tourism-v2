<?php

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\Entity\User;
use App\Service\DestinationCommentService;
use App\Service\DestinationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DestinationCommentController extends BaseController
{
    public function __construct(
        private DestinationCommentService $commentService,
        private DestinationService $destinationService
    )
    {
    }

    #[Route(path: '/api/comments', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $comment = $this->commentService->create(request: $request, user: $user);

        if ($comment instanceof ErrorResponse) {
            return $this->json($comment, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->jsonCommentRead(comment: $comment, status: Response::HTTP_CREATED);
    }

    #[Route(path: '/api/comments/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $comment = $this->commentService->get(commentId: $id);

        if (!$comment) {
            return $this->json(new ErrorResponse(
                message: 'Deleting error',
                errors: ['comment' => 'not found']
            ), Response::HTTP_NOT_FOUND);
        }

        $destination = $this->destinationService->findById(id: $comment->getDestination()?->getId());

        $this->commentService->delete(commentId: $id);

        return $this->jsonDestinationRead(destination: $destination);
    }

    #[Route(path: '/api/comments/search', methods: ['GET'])]
    public function byDestination(Request $request): JsonResponse
    {
        return $this->jsonCommentRead(comment: $this->commentService->search(request: $request));
    }

    #[Route(path: '/api/comments/{id}', methods: ['PATCH'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $comment = $this->commentService->patch(id: $id, request: $request);

        if ($comment instanceof ErrorResponse) {
            return $this->json($comment, Response::HTTP_NOT_ACCEPTABLE);
        }

        return $this->jsonCommentRead(comment: $comment, status: Response::HTTP_ACCEPTED);
    }
}