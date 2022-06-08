<?php

namespace App\Service;

use App\DTO\ErrorResponse;
use App\Entity\Destination;
use App\Entity\User;
use App\Entity\WishList;
use App\Repository\WishListRepository;
use Symfony\Component\HttpFoundation\Request;

class ListService
{
    public function __construct(
        private Crud $crud,
        private WishListRepository $listRepository,
        private AuthorizationService $authorizationService
    )
    {
    }

    public function getById(int $id): ?WishList
    {
        return $this->listRepository->find($id);
    }

    public function create(Request $request, User $user): ErrorResponse|WishList
    {
        $list = $this->crud->deserializeEntity(request: $request, entityClass: WishList::class);

        if ($list instanceof ErrorResponse) {
            return $list;
        }

        if (!$list instanceof WishList) {
            return new ErrorResponse(message: 'Creation failed', errors: ['request' => 'bad request']);
        }

        if (!in_array($list->getType(), [WishList::FAVORITES, WishList::TO_VISIT], true)) {
            return new ErrorResponse(message: 'Creation failed', errors: ['list' => 'wrong type']);
        }

        foreach ($user->getWishLists() as $wishList) {
            if ($list->getName() === $wishList->getName()) {
                return new ErrorResponse(message: 'Creation failed', errors: ['list' => 'already exists']);
            }
        }

        $list->setUser($user);

        $this->crud->create(entity: $list);

        return $list;
    }

    public function appendDestination(Request $request, User $user): ErrorResponse|WishList
    {
        $destination = $this->crud->extractDestinationFromRequest(request: $request);

        if ($destination instanceof ErrorResponse) {
            return $destination;
        }

        $list = $this->crud->extractListFromRequest(request: $request);

        if ($list instanceof ErrorResponse) {
            return $list;
        }

        if (!$this->authorizationService->authorizeList(list: $list)) {
            return new ErrorResponse(
                message: 'Access error',
                errors: ['list' => 'not valid owner']
            );
        }

        $list->addDestination(destination: $destination);

        $this->crud->patch(entity: $list);

        return $list;
    }

    public function removeDestination(WishList $list, Destination $destination): ErrorResponse|WishList
    {
        if (!$this->authorizationService->authorizeList(list: $list)) {
            return new ErrorResponse(
                message: 'Access error',
                errors: ['list' => 'not valid owner']
            );
        }

        $list->removeDestination(destination: $destination);

        $this->crud->patch(entity: $list);

        return $list;
    }

    public function delete(WishList $list): void
    {
        if (!$this->authorizationService->authorizeList(list: $list)) {
            $this->crud->remove(entity: $list);
        }
    }

}