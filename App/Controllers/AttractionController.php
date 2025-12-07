<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\Attraction;

class AttractionController extends BaseController
{
    /**
     * Autorizácia - index a show sú verejné, ostatné len pre adminov
     */
    public function authorize(Request $request, string $action): bool
    {
        // Verejné akcie
        if (in_array($action, ['index', 'show'])) {
            return true;
        }
        
        // Ostatné len pre adminov
        return $this->checkAdmin();
    }

    /**
     * Zobrazenie zoznamu atrakcií
     */
    public function index(Request $request): Response
    {
        $typ = $request->value('typ');
        
        if ($typ) {
            $attractions = Attraction::getByType($typ);
        } else {
            $attractions = Attraction::getAllAttractions();
        }

        $types = Attraction::getAllTypes();
        
        return $this->html([
            'attractions' => $attractions,
            'types' => $types,
            'selectedType' => $typ
        ]);
    }

    /**
     * Zobrazenie detailu atrakcie
     */
    public function show(Request $request): Response
    {
        $id = (int)$request->value('id');
        $attraction = Attraction::getOne($id);
        
        if (!$attraction) {
            return $this->redirect($this->url('attraction.index', ['error' => 'not_found']));
        }

        $nearbyAccommodations = $attraction->getNearbyAccommodations();
        
        return $this->html([
            'attraction' => $attraction,
            'nearbyAccommodations' => $nearbyAccommodations
        ]);
    }

    /**
     * Zobrazenie formulára pre vytvorenie atrakcie (admin)
     */
    public function create(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        return $this->html();
    }

    /**
     * Uloženie novej atrakcie
     */
    public function store(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $errors = $this->validate($request);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'old' => $request->post()
            ], viewName: 'create');
        }

        $attraction = new Attraction();
        $attraction->nazov = htmlspecialchars(trim($request->value('nazov')));
        $attraction->popis = htmlspecialchars(trim($request->value('popis')));
        $attraction->typ = htmlspecialchars(trim($request->value('typ')));
        $attraction->cena = (int)$request->value('cena');
        $attraction->poloha = htmlspecialchars(trim($request->value('poloha')));
        $attraction->obrazok = $request->value('obrazok');

        if ($attraction->save()) {
            return $this->redirect($this->url('attraction.index', ['success' => 'created']));
        } else {
            return $this->redirect($this->url('attraction.create', ['error' => 'failed']));
        }
    }

    /**
     * Zobrazenie formulára pre editáciu
     */
    public function edit(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $id = (int)$request->value('id');
        $attraction = Attraction::getOne($id);
        
        if (!$attraction) {
            return $this->redirect($this->url('attraction.index', ['error' => 'not_found']));
        }
        
        return $this->html(['attraction' => $attraction]);
    }

    /**
     * Aktualizácia atrakcie
     */
    public function update(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $id = (int)$request->value('id');
        $attraction = Attraction::getOne($id);
        
        if (!$attraction) {
            return $this->redirect($this->url('attraction.index', ['error' => 'not_found']));
        }

        $errors = $this->validate($request);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'attraction' => $attraction,
                'old' => $request->post()
            ], viewName: 'edit');
        }

        $attraction->nazov = htmlspecialchars(trim($request->value('nazov')));
        $attraction->popis = htmlspecialchars(trim($request->value('popis')));
        $attraction->typ = htmlspecialchars(trim($request->value('typ')));
        $attraction->cena = (int)$request->value('cena');
        $attraction->poloha = htmlspecialchars(trim($request->value('poloha')));
        $attraction->obrazok = $request->value('obrazok');

        if ($attraction->save()) {
            return $this->redirect($this->url('attraction.index', ['success' => 'updated']));
        } else {
            return $this->redirect($this->url('attraction.edit', ['id' => $id, 'error' => 'failed']));
        }
    }

    /**
     * Vymazanie atrakcie
     */
    public function delete(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $id = (int)$request->value('id');
        $attraction = Attraction::getOne($id);
        
        if (!$attraction) {
            return $this->redirect($this->url('attraction.index', ['error' => 'not_found']));
        }

        if ($attraction->delete()) {
            return $this->redirect($this->url('attraction.index', ['success' => 'deleted']));
        } else {
            return $this->redirect($this->url('attraction.index', ['error' => 'delete_failed']));
        }
    }

    /**
     * Kontrola či je používateľ admin
     */
    private function checkAdmin(): bool
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return false;
        }

        $user = \App\Models\User::getOne($this->app->getAuthenticator()->getUser()->getId());
        return $user && $user->isAdmin();
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

        $popis = trim($request->value('popis'));
        if (empty($popis) || strlen($popis) < 10) {
            $errors['popis'] = 'Popis musí mať minimálne 10 znakov';
        }

        $cena = $request->value('cena');
        if ($cena === null || $cena < 0) {
            $errors['cena'] = 'Cena musí byť 0 alebo väčšia';
        }

        return $errors;
    }
}
