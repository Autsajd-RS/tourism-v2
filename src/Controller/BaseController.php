<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Destination;
use App\Entity\DestinationComment;
use App\Entity\User;
use App\Entity\WishList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    /**
     * @param User|User[] $user
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonUserRead(User|array $user, int $status = 200): JsonResponse
    {
        return $this->json($user, $status, [], ['groups' => User::GROUP_READ]);
    }

    /**
     * @param City|City[] $city
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonCityRead(City|array $city, int $status = 200): JsonResponse
    {
        return $this->json($city, $status, [], ['groups' => City::SERIALIZER_GROUP_CITY_LIST]);
    }

    /**
     * @param Category|Category[] $category
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonCategoryRead(Category|array $category, int $status = 200): JsonResponse
    {
        return $this->json($category, $status, [], ['groups' => Category::GROUP_READ]);
    }

    /**
     * @param Destination|Destination[] $destination
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonDestinationRead(Destination|array $destination, int $status = 200): JsonResponse
    {
        return $this->json($destination, $status, [], ['groups' => Destination::GROUP_READ]);
    }

    /**
     * @param DestinationComment|array $comment
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonCommentRead(DestinationComment|array $comment, int $status = 200): JsonResponse
    {
        return $this->json($comment, $status, [], ['groups' => DestinationComment::GROUP_READ]);
    }

    /**
     * @param WishList|array $wishList
     * @param int $status
     * @return JsonResponse
     */
    protected function jsonListRead(WishList|array $wishList, int $status = 200): JsonResponse
    {
        return $this->json($wishList, $status, [], ['groups' => WishList::GROUP_READ]);
    }
}