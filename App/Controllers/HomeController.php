<?php

namespace App\Controllers;

use App\Models\Attraction;
use App\Models\Accommodation;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class HomeController
 * Kontrolér pre domovskú stránku a verejné stránky
 */
class HomeController extends BaseController
{
    /**
     * Autorizácia - všetky akcie sú verejné
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Domovská stránka
     */
    public function index(Request $request): Response
    {
        // Načítanie niektorých atrakcií pre domovskú stránku
        $allAttractions = Attraction::getAllAttractions();
        $featuredAttractions = array_slice($allAttractions, 0, 3);
        
        // Načítanie niektorých ubytovaní
        $allAccommodations = Accommodation::getAllActive();
        $featuredAccommodations = array_slice($allAccommodations, 0, 3);
        
        return $this->html([
            'featuredAttractions' => $featuredAttractions,
            'featuredAccommodations' => $featuredAccommodations
        ]);
    }

    /**
     * Kontaktná stránka
     *
     * @return Response The response object containing the rendered HTML for the contact page.
     */
    public function contact(Request $request): Response
    {
        return $this->html();
    }
}
