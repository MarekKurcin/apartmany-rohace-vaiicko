<?php

namespace App\Controllers;

use App\Models\Attraction;
use App\Models\Accommodation;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class HomeController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    public function index(Request $request): Response
    {
        $allAttractions = Attraction::getAllAttractions();
        $featuredAttractions = array_slice($allAttractions, 0, 3);

        $allAccommodations = Accommodation::getAllActive();
        $featuredAccommodations = array_slice($allAccommodations, 0, 3);

        return $this->html([
            'featuredAttractions' => $featuredAttractions,
            'featuredAccommodations' => $featuredAccommodations
        ]);
    }

    public function contact(Request $request): Response
    {
        return $this->html();
    }
}
