<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\Attraction;

class AttractionController extends BaseController
{
    /**
     * Autorizácia - index, show a filterAjax sú verejné, ostatné len pre adminov
     */
    public function authorize(Request $request, string $action): bool
    {
        // Verejné akcie
        if (in_array($action, ['index', 'show', 'filterAjax'])) {
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
        $filters = [
            'typ' => $request->value('typ'),
            'cena_filter' => $request->value('cena_filter'),
            'zoradenie' => $request->value('zoradenie') ?: 'najnovsie'
        ];

        $attractions = Attraction::searchWithFilters($filters);
        $types = Attraction::getAllTypes();

        return $this->html([
            'attractions' => $attractions,
            'types' => $types,
            'filters' => $filters
        ]);
    }

    /**
     * AJAX API - Filtrovanie atrakcií
     */
    public function filterAjax(Request $request): JsonResponse
    {
        $filters = [
            'typ' => $request->value('typ'),
            'cena_filter' => $request->value('cena_filter'),
            'zoradenie' => $request->value('zoradenie') ?: 'najnovsie'
        ];

        $attractions = Attraction::searchWithFilters($filters);

        $result = [];
        foreach ($attractions as $attr) {
            $result[] = [
                'id' => $attr->id,
                'nazov' => $attr->nazov,
                'popis' => $attr->popis ? substr($attr->popis, 0, 120) . (strlen($attr->popis) > 120 ? '...' : '') : '',
                'typ' => $attr->typ,
                'cena' => $attr->cena,
                'cena_formatted' => $attr->getFormattedPrice(),
                'poloha' => $attr->poloha,
                'obrazok' => $attr->obrazok ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'is_free' => $attr->isFree()
            ];
        }

        return new JsonResponse([
            'success' => true,
            'count' => count($result),
            'data' => $result
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

        $imageResult = $this->handleImageUpload();
        if ($imageResult['error']) {
            $errors['obrazok'] = $imageResult['error'];
        }

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'old' => $request->post()
            ], viewName: 'create');
        }

        // Urcenie obrazka - priorita: upload > URL
        $obrazok = null;
        if ($imageResult['path']) {
            $obrazok = $imageResult['path'];
        } elseif ($request->value('obrazok_url')) {
            $obrazok = htmlspecialchars(trim($request->value('obrazok_url')));
        }

        $attraction = new Attraction();
        $attraction->nazov = htmlspecialchars(trim($request->value('nazov')));
        $attraction->popis = htmlspecialchars(trim($request->value('popis')));
        $attraction->typ = htmlspecialchars(trim($request->value('typ') ?? ""));
        $attraction->cena = (int)$request->value('cena') ?? "0";
        $attraction->poloha = htmlspecialchars(trim($request->value('poloha') ?? ""));
        $attraction->obrazok = $obrazok;

        try {
            $attraction->save();
            return $this->redirect($this->url('attraction.index', ['success' => 'created']));
        } catch (\Exception $e) {
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

        $imageResult = $this->handleImageUpload();
        if ($imageResult['error']) {
            $errors['obrazok'] = $imageResult['error'];
        }

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

        // Aktualizacia obrazka - priorita: upload > URL > ponechat povodny
        if ($imageResult['path']) {
            $this->deleteOldImage($attraction->obrazok);
            $attraction->obrazok = $imageResult['path'];
        } elseif ($request->value('obrazok_url')) {
            $this->deleteOldImage($attraction->obrazok);
            $attraction->obrazok = htmlspecialchars(trim($request->value('obrazok_url')));
        }

        try {
            $attraction->save();
            return $this->redirect($this->url('attraction.index', ['success' => 'updated']));
        } catch (\Exception $e) {
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

        try {
            $this->deleteOldImage($attraction->obrazok);
            $attraction->delete();
            return $this->redirect($this->url('attraction.index', ['success' => 'deleted']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('attraction.index', ['error' => 'delete_failed']));
        }
    }

    /**
     * Spracovanie uploadu obrazka
     * @return array ['path' => string|null, 'error' => string|null]
     */
    private function handleImageUpload(): array
    {
        if (!isset($_FILES['obrazok']) || $_FILES['obrazok']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        $file = $_FILES['obrazok'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'Chyba pri nahravani suboru'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['path' => null, 'error' => 'Povolene su len JPG, PNG a WebP obrazky'];
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['path' => null, 'error' => 'Maximalna velkost suboru je 5MB'];
        }

        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $extensions[$mimeType];
        $newFilename = uniqid('attr_') . '_' . time() . '.' . $extension;

        $uploadDir = __DIR__ . '/../../public/uploads/attractions/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['path' => '/uploads/attractions/' . $newFilename, 'error' => null];
        }

        return ['path' => null, 'error' => 'Nepodarilo sa ulozit subor'];
    }

    /**
     * Vymazanie stareho obrazka
     */
    private function deleteOldImage(?string $imagePath): void
    {
        if ($imagePath && strpos($imagePath, '/uploads/attractions/') === 0) {
            $fullPath = __DIR__ . '/../../public' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
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

        return $errors;
    }
}
