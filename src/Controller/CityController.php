<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends BaseController
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {

        $this->cityRepository = $cityRepository;
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: "/cities", methods: "GET")]
    public function getAllCities(): JsonResponse
    {
        $cities = $this->cityRepository->findAll();

        return $this->jsonCityRead($cities);
    }
}