<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class AdminController
 *
 * This controller manages admin-related actions within the application.It extends the base controller functionality
 * provided by BaseController.
 *
 * @package App\Controllers
 */
class AdminController extends BaseController
{
    /**
     * Authorizes actions in this controller.
     *
     * This method checks if the user is logged in and has admin role.
     *
     * @param string $action The name of the action to authorize.
     * @return bool Returns true if the user is logged in and is admin; false otherwise.
     */
    public function authorize(Request $request, string $action): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        
        // Získať používateľa z databázy
        $user = \App\Models\User::getOne($this->user->getId());
        
        return $user && $user->isAdmin();
    }

    /**
     * Displays the index page of the admin panel.
     *
     * This action requires authorization. It returns an HTML response for the admin dashboard or main page.
     *
     * @return Response Returns a response object containing the rendered HTML.
     */
    public function index(Request $request): Response
    {
        $currentUser = \App\Models\User::getOne($this->user->getId());
        
        // Získať štatistiky
        $stats = [
            'totalUsers' => count(\App\Models\User::getAll()),
            'totalAccommodations' => count(\App\Models\Accommodation::getAll()),
            'totalAttractions' => count(\App\Models\Attraction::getAll()),
            'totalReservations' => count(\App\Models\Reservation::getAll()),
            'totalReviews' => count(\App\Models\Review::getAll())
        ];
        
        // Získať posledných používateľov
        $recentUsers = \App\Models\User::getAll(null, [], 'datum_vytvorenia DESC', 5);
        
        // Získať posledné ubytovania
        $recentAccommodations = \App\Models\Accommodation::getAll(null, [], 'id DESC', 5);
        
        return $this->html([
            'currentUser' => $currentUser,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentAccommodations' => $recentAccommodations
        ]);
    }
}
