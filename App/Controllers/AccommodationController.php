<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Accommodation;
use App\Models\User;

class AccommodationController extends BaseController
{
    /**
     * Autorizácia - index a show sú verejné, ostatné vyžadujú prihlásenie
     */
    public function authorize(Request $request, string $action): bool
    {
        // Verejné akcie
        if (in_array($action, ['index', 'show'])) {
            return true;
        }
        
        // Ostatné vyžadujú prihlásenie
        return $this->app->getAuthenticator()->getUser()->isLoggedIn();
    }

    /**
     * Zobrazenie zoznamu ubytovaní
     */
    public function index(Request $request): Response
    {
        // Získanie filtrov z GET
        $filters = [
            'kapacita' => $request->value('kapacita'),
            'max_cena' => $request->value('max_cena'),
            'vybavenie' => $request->value('vybavenie')
        ];
        
        // Ak sú zadané filtre, použijeme vyhľadávanie
        if (!empty($filters['kapacita']) || !empty($filters['max_cena']) || !empty($filters['vybavenie'])) {
            $accommodations = Accommodation::search($filters);
        } else {
            $accommodations = Accommodation::getAllActive();
        }
        
        return $this->html([
            'accommodations' => $accommodations,
            'filters' => $filters
        ]);
    }

    /**
     * Zobrazenie detailu ubytovania
     */
    public function show(Request $request): Response
    {
        $id = (int)$request->value('id');
        $accommodation = Accommodation::getOne($id);
        
        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        $attractions = $accommodation->getAttractions();
        $reviews = $accommodation->getReviews();
        $averageRating = $accommodation->getAverageRating();
        
        return $this->html([
            'accommodation' => $accommodation,
            'attractions' => $attractions,
            'reviews' => $reviews,
            'averageRating' => $averageRating
        ]);
    }

    /**
     * Zobrazenie formulára pre vytvorenie
     */
    public function create(Request $request): Response
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login', ['error' => 'not_logged']));
        }

        return $this->html();
    }

    /**
     * Uloženie nového ubytovania
     */
    public function store(Request $request): Response
    {
        // Kontrola prihlásenia
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $errors = $this->validate($request);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'old' => $request->post()
            ], viewName: 'create');
        }

        $accommodation = new Accommodation();
        $accommodation->user_id = $this->app->getAuthenticator()->getUser()->getId();
        $accommodation->nazov = htmlspecialchars(trim($request->value('nazov')));
        $accommodation->popis = htmlspecialchars(trim($request->value('popis')));
        $accommodation->adresa = htmlspecialchars(trim($request->value('adresa')));
        $accommodation->kapacita = (int)$request->value('kapacita');
        $accommodation->cena_za_noc = (float)$request->value('cena_za_noc');
        $accommodation->vybavenie = htmlspecialchars(trim($request->value('vybavenie')));
        $accommodation->obrazok = $request->value('obrazok');
        $accommodation->aktivne = true;

        if ($accommodation->save()) {
            return $this->redirect($this->url('accommodation.index', ['success' => 'created']));
        } else {
            return $this->redirect($this->url('accommodation.create', ['error' => 'failed']));
        }
    }

    /**
     * Zobrazenie formulára pre editáciu
     */
    public function edit(Request $request): Response
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $id = (int)$request->value('id');
        $accommodation = Accommodation::getOne($id);
        
        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        // Kontrola oprávnenia (len vlastník alebo admin)
        $user = User::getOne($this->app->getAuthenticator()->getUser()->getId());
        if ($accommodation->user_id != $user->id && !$user->isAdmin()) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'unauthorized']));
        }
        
        return $this->html(['accommodation' => $accommodation]);
    }

    /**
     * Aktualizácia ubytovania
     */
    public function update(Request $request): Response
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $id = (int)$request->value('id');
        $accommodation = Accommodation::getOne($id);
        
        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        // Kontrola oprávnenia
        $user = User::getOne($this->app->getAuthenticator()->getUser()->getId());
        if ($accommodation->user_id != $user->id && !$user->isAdmin()) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'unauthorized']));
        }

        $errors = $this->validate($request);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'accommodation' => $accommodation,
                'old' => $request->post()
            ], viewName: 'edit');
        }

        $accommodation->nazov = htmlspecialchars(trim($request->value('nazov')));
        $accommodation->popis = htmlspecialchars(trim($request->value('popis')));
        $accommodation->adresa = htmlspecialchars(trim($request->value('adresa')));
        $accommodation->kapacita = (int)$request->value('kapacita');
        $accommodation->cena_za_noc = (float)$request->value('cena_za_noc');
        $accommodation->vybavenie = htmlspecialchars(trim($request->value('vybavenie')));
        $accommodation->obrazok = $request->value('obrazok');
        $accommodation->aktivne = $request->value('aktivne') ? true : false;

        if ($accommodation->save()) {
            return $this->redirect($this->url('accommodation.index', ['success' => 'updated']));
        } else {
            return $this->redirect($this->url('accommodation.edit', ['id' => $id, 'error' => 'failed']));
        }
    }

    /**
     * Vymazanie ubytovania
     */
    public function delete(Request $request): Response
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $id = (int)$request->value('id');
        $accommodation = Accommodation::getOne($id);
        
        if (!$accommodation) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'not_found']));
        }

        // Kontrola oprávnenia
        $user = User::getOne($this->app->getAuthenticator()->getUser()->getId());
        if ($accommodation->user_id != $user->id && !$user->isAdmin()) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'unauthorized']));
        }

        if ($accommodation->delete()) {
            return $this->redirect($this->url('accommodation.index', ['success' => 'deleted']));
        } else {
            return $this->redirect($this->url('accommodation.index', ['error' => 'delete_failed']));
        }
    }

    /**
     * Validácia dát
     */
    private function validate(Request $request): array
    {
        $errors = [];

        $nazov = trim($request->value('nazov'));
        if (empty($nazov) || strlen($nazov) < 3) {
            $errors['nazov'] = 'Názov musí mať minimálne 3 znaky';
        }

        $adresa = trim($request->value('adresa'));
        if (empty($adresa) || strlen($adresa) < 5) {
            $errors['adresa'] = 'Adresa musí mať minimálne 5 znakov';
        }

        $kapacita = (int)$request->value('kapacita');
        if ($kapacita < 1 || $kapacita > 50) {
            $errors['kapacita'] = 'Kapacita musí byť medzi 1 a 50';
        }

        $cena = (float)$request->value('cena_za_noc');
        if ($cena <= 0) {
            $errors['cena_za_noc'] = 'Cena musí byť väčšia ako 0';
        }

        return $errors;
    }
}
