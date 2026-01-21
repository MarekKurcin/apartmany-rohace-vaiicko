<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class AdminController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }

        $user = \App\Models\User::getOne($this->user->getId());

        return $user && $user->isAdmin();
    }

    public function index(Request $request): Response
    {
        $currentUser = \App\Models\User::getOne($this->user->getId());

        $stats = [
            'totalUsers' => count(\App\Models\User::getAll()),
            'totalAccommodations' => count(\App\Models\Accommodation::getAll()),
            'totalAttractions' => count(\App\Models\Attraction::getAll()),
            'totalReservations' => count(\App\Models\Reservation::getAll()),
            'totalReviews' => count(\App\Models\Review::getAll())
        ];

        $recentUsers = \App\Models\User::getAll(null, [], 'datum_vytvorenia DESC', 5);

        $recentAccommodations = \App\Models\Accommodation::getAll(null, [], 'id DESC', 5);

        return $this->html([
            'currentUser' => $currentUser,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentAccommodations' => $recentAccommodations
        ]);
    }
}
