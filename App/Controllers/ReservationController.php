<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Reservation;
use App\Models\Accommodation;
use App\Models\User;

class ReservationController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return $this->app->getAuthenticator()->getUser()->isLoggedIn();
    }

    public function index(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $reservations = Reservation::getByUser($userId);

        return $this->html([
            'reservations' => $reservations
        ]);
    }

    public function create(Request $request): Response
    {
        $accommodationId = (int)$request->value('id');
        $accommodation = Accommodation::getOne($accommodationId);

        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        return $this->html([
            'accommodation' => $accommodation
        ]);
    }

    public function store(Request $request): Response
    {
        $accommodationId = (int)$request->value('accommodation_id');
        $accommodation = Accommodation::getOne($accommodationId);

        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        $errors = $this->validate($request, $accommodation);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'accommodation' => $accommodation,
                'old' => $request->post()
            ], viewName: 'create');
        }

        $datumOd = $request->value('datum_od');
        $datumDo = $request->value('datum_do');
        $pocetOsob = (int)$request->value('pocet_osob');

        if (!Reservation::isAvailable($accommodationId, $datumOd, $datumDo)) {
            return $this->html([
                'errors' => ['datum' => 'Vybraný termín nie je dostupný'],
                'accommodation' => $accommodation,
                'old' => $request->post()
            ], viewName: 'create');
        }

        $reservation = new Reservation();
        $reservation->user_id = $this->app->getAuthenticator()->getUser()->getId();
        $reservation->accommodation_id = $accommodationId;
        $reservation->datum_od = $datumOd;
        $reservation->datum_do = $datumDo;
        $reservation->pocet_osob = $pocetOsob;
        $reservation->celkova_cena = $reservation->calculateTotalPrice($accommodation->cena_za_noc);
        $reservation->stav = 'cakajuca';

        try {
            $reservation->save();
            return $this->redirect($this->url('reservation.index', ['success' => 'created']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('reservation.create', ['id' => $accommodationId, 'error' => 'failed']));
        }
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->value('id');
        $reservation = Reservation::getOne($id);

        if (!$reservation) {
            return $this->redirect($this->url('reservation.index', ['error' => 'not_found']));
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);
        $accommodation = $reservation->getAccommodation();

        if ($reservation->user_id != $userId && $accommodation->user_id != $userId && !$user->isAdmin()) {
            return $this->redirect($this->url('reservation.index', ['error' => 'unauthorized']));
        }

        return $this->html([
            'reservation' => $reservation,
            'accommodation' => $accommodation,
            'guest' => $reservation->getUser()
        ]);
    }

    public function cancel(Request $request): Response
    {
        $id = (int)$request->value('id');
        $reservation = Reservation::getOne($id);

        if (!$reservation) {
            return $this->redirect($this->url('reservation.index', ['error' => 'not_found']));
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();

        if ($reservation->user_id != $userId) {
            return $this->redirect($this->url('reservation.index', ['error' => 'unauthorized']));
        }

        if (!in_array($reservation->stav, ['cakajuca', 'potvrdena'])) {
            return $this->redirect($this->url('reservation.index', ['error' => 'cannot_cancel']));
        }

        try {
            $reservation->cancel();
            return $this->redirect($this->url('reservation.index', ['success' => 'cancelled']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('reservation.index', ['error' => 'failed']));
        }
    }

    public function manage(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);

        if (!$user->isUbytovatel()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $accommodations = Accommodation::getAll("user_id = ?", [$userId]);
        $accommodationIds = array_map(fn($a) => $a->id, $accommodations);

        $reservations = [];
        foreach ($accommodations as $accommodation) {
            $accReservations = Reservation::getByAccommodation($accommodation->id);
            foreach ($accReservations as $res) {
                $reservations[] = $res;
            }
        }

        usort($reservations, function($a, $b) {
            return strtotime($b->datum_od) - strtotime($a->datum_od);
        });

        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        $monthlyStats = Reservation::getMonthlyStats($accommodationIds, $currentYear);
        $occupancy = Reservation::getOccupancyForMonth($accommodationIds, $currentYear, $currentMonth);

        $accommodationStats = [];
        foreach ($accommodations as $acc) {
            $accommodationStats[$acc->id] = Reservation::getAccommodationStats($acc->id);
            $accommodationStats[$acc->id]['nazov'] = $acc->nazov;
        }

        return $this->html([
            'reservations' => $reservations,
            'accommodations' => $accommodations,
            'monthlyStats' => $monthlyStats,
            'occupancy' => $occupancy,
            'accommodationStats' => $accommodationStats,
            'currentYear' => $currentYear
        ]);
    }

    public function confirm(Request $request): Response
    {
        $id = (int)$request->value('id');
        $reservation = Reservation::getOne($id);

        if (!$reservation) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'not_found']));
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);
        $accommodation = $reservation->getAccommodation();

        if ($accommodation->user_id != $userId && !$user->isAdmin()) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'unauthorized']));
        }

        if ($reservation->stav !== 'cakajuca') {
            return $this->redirect($this->url('reservation.manage', ['error' => 'invalid_status']));
        }

        try {
            $reservation->confirm();
            return $this->redirect($this->url('reservation.manage', ['success' => 'confirmed']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'failed']));
        }
    }

    public function reject(Request $request): Response
    {
        $id = (int)$request->value('id');
        $reservation = Reservation::getOne($id);

        if (!$reservation) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'not_found']));
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);
        $accommodation = $reservation->getAccommodation();

        if ($accommodation->user_id != $userId && !$user->isAdmin()) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'unauthorized']));
        }

        if ($reservation->stav !== 'cakajuca') {
            return $this->redirect($this->url('reservation.manage', ['error' => 'invalid_status']));
        }

        try {
            $reservation->cancel();
            return $this->redirect($this->url('reservation.manage', ['success' => 'rejected']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('reservation.manage', ['error' => 'failed']));
        }
    }

    public function exportCsv(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);

        if (!$user->isUbytovatel()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $accommodations = Accommodation::getAll("user_id = ?", [$userId]);

        $reservations = [];
        foreach ($accommodations as $accommodation) {
            $accReservations = Reservation::getByAccommodation($accommodation->id);
            foreach ($accReservations as $res) {
                $reservations[] = $res;
            }
        }

        usort($reservations, function($a, $b) {
            return strtotime($b->datum_od) - strtotime($a->datum_od);
        });

        $filename = 'rezervacie_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Ubytovanie',
            'Hosť',
            'Email',
            'Dátum od',
            'Dátum do',
            'Počet nocí',
            'Počet osôb',
            'Celková cena',
            'Stav'
        ], ';');

        foreach ($reservations as $res) {
            $accommodation = $res->getAccommodation();
            $guest = $res->getUser();

            fputcsv($output, [
                $res->id,
                $accommodation ? $accommodation->nazov : '-',
                $guest ? $guest->getFullName() : '-',
                $guest ? $guest->email : '-',
                date('d.m.Y', strtotime($res->datum_od)),
                date('d.m.Y', strtotime($res->datum_do)),
                $res->getNightsCount(),
                $res->pocet_osob,
                number_format($res->celkova_cena, 2, ',', ''),
                $res->getStatusLabel()
            ], ';');
        }

        fclose($output);
        exit;
    }

    private function validate(Request $request, Accommodation $accommodation): array
    {
        $errors = [];

        $datumOd = $request->value('datum_od');
        $datumDo = $request->value('datum_do');
        $pocetOsob = (int)$request->value('pocet_osob');

        if (empty($datumOd)) {
            $errors['datum_od'] = 'Zadajte dátum príchodu';
        }

        if (empty($datumDo)) {
            $errors['datum_do'] = 'Zadajte dátum odchodu';
        }

        if (!empty($datumOd) && !empty($datumDo)) {
            $od = strtotime($datumOd);
            $do = strtotime($datumDo);
            $dnes = strtotime(date('Y-m-d'));

            if ($od < $dnes) {
                $errors['datum_od'] = 'Dátum príchodu nemôže byť v minulosti';
            }

            if ($do <= $od) {
                $errors['datum_do'] = 'Dátum odchodu musí byť po dátume príchodu';
            }
        }

        if ($pocetOsob < 1) {
            $errors['pocet_osob'] = 'Zadajte počet osôb';
        } elseif ($pocetOsob > $accommodation->kapacita) {
            $errors['pocet_osob'] = 'Maximálna kapacita je ' . $accommodation->kapacita . ' osôb';
        }

        return $errors;
    }
}
