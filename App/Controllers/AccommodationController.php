<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\Accommodation;
use App\Models\Review;
use App\Models\User;

class AccommodationController extends BaseController
{
    /**
     * Autorizácia - index a show sú verejné, ostatné vyžadujú prihlásenie
     */
    public function authorize(Request $request, string $action): bool
    {
        // Verejné akcie (vrátane AJAX filtrovania)
        if (in_array($action, ['index', 'show', 'filterAjax'])) {
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

        $accommodation = new Accommodation();
        $accommodation->user_id = $this->app->getAuthenticator()->getUser()->getId();
        $accommodation->nazov = htmlspecialchars(trim($request->value('nazov')));
        $accommodation->popis = htmlspecialchars(trim($request->value('popis')));
        $accommodation->adresa = htmlspecialchars(trim($request->value('adresa')));
        $accommodation->kapacita = (int)$request->value('kapacita');
        $accommodation->cena_za_noc = (float)$request->value('cena_za_noc');
        $accommodation->vybavenie = htmlspecialchars(trim($request->value('vybavenie')));
        $accommodation->obrazok = $obrazok;
        $accommodation->aktivne = true;

        try {
            $accommodation->save();
            return $this->redirect($this->url('accommodation.index', ['success' => 'created']));
        } catch (\Exception $e) {
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

        $imageResult = $this->handleImageUpload();
        if ($imageResult['error']) {
            $errors['obrazok'] = $imageResult['error'];
        }

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

        // Aktualizacia obrazka - priorita: upload > URL > ponechat povodny
        if ($imageResult['path']) {
            $this->deleteOldImage($accommodation->obrazok);
            $accommodation->obrazok = $imageResult['path'];
        } elseif ($request->value('obrazok_url')) {
            $this->deleteOldImage($accommodation->obrazok);
            $accommodation->obrazok = htmlspecialchars(trim($request->value('obrazok_url')));
        }

        $accommodation->aktivne = $request->value('aktivne') ? true : false;

        try {
            $accommodation->save();
            return $this->redirect($this->url('accommodation.index', ['success' => 'updated']));
        } catch (\Exception $e) {
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

        try {
            $this->deleteOldImage($accommodation->obrazok);
            $accommodation->delete();
            return $this->redirect($this->url('accommodation.index', ['success' => 'deleted']));
        } catch (\Exception $e) {
            return $this->redirect($this->url('accommodation.index', ['error' => 'delete_failed']));
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
        $newFilename = uniqid('acc_') . '_' . time() . '.' . $extension;

        $uploadDir = __DIR__ . '/../../public/uploads/accommodations/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['path' => '/uploads/accommodations/' . $newFilename, 'error' => null];
        }

        return ['path' => null, 'error' => 'Nepodarilo sa ulozit subor'];
    }

    /**
     * Vymazanie stareho obrazka
     */
    private function deleteOldImage(?string $imagePath): void
    {
        if ($imagePath && strpos($imagePath, '/uploads/accommodations/') === 0) {
            $fullPath = __DIR__ . '/../../public' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
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

    /**
     * AJAX API - Filtrovanie ubytovaní
     * Vracia JSON s ubytovaniami podľa filtrov
     */
    public function filterAjax(Request $request): JsonResponse
    {
        $filters = [
            'kapacita' => $request->value('kapacita'),
            'max_cena' => $request->value('max_cena'),
            'vybavenie' => $request->value('vybavenie')
        ];

        if (!empty($filters['kapacita']) || !empty($filters['max_cena']) || !empty($filters['vybavenie'])) {
            $accommodations = Accommodation::search($filters);
        } else {
            $accommodations = Accommodation::getAllActive();
        }

        $result = [];
        foreach ($accommodations as $acc) {
            $result[] = [
                'id' => $acc->id,
                'nazov' => $acc->nazov,
                'popis' => $acc->popis ? substr($acc->popis, 0, 100) . (strlen($acc->popis) > 100 ? '...' : '') : '',
                'adresa' => $acc->adresa,
                'kapacita' => $acc->kapacita,
                'cena_za_noc' => number_format($acc->cena_za_noc, 2),
                'obrazok' => $acc->obrazok ?? 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800',
                'vybavenie' => $acc->getVybavenieArray()
            ];
        }

        return new JsonResponse([
            'success' => true,
            'count' => count($result),
            'data' => $result
        ]);
    }

    /**
     * AJAX API - Pridanie recenzie
     * Uloží novú recenziu a vráti JSON odpoveď
     */
    public function storeReview(Request $request): JsonResponse
    {
        // Kontrola prihlásenia
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Pre pridanie recenzie sa musíte prihlásiť'
            ]);
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $accommodationId = (int)$request->value('accommodation_id');
        $hodnotenie = (int)$request->value('hodnotenie');
        $komentar = trim($request->value('komentar') ?? '');

        // Validácia
        if ($accommodationId <= 0) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Neplatné ubytovanie'
            ]);
        }

        if ($hodnotenie < 1 || $hodnotenie > 5) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Hodnotenie musí byť od 1 do 5'
            ]);
        }

        // Kontrola či ubytovanie existuje
        $accommodation = Accommodation::getOne($accommodationId);
        if (!$accommodation) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ubytovanie neexistuje'
            ]);
        }

        // Kontrola či už používateľ nehodnotil
        if (Review::hasUserReviewed($userId, $accommodationId)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Toto ubytovanie ste už hodnotili'
            ]);
        }

        // Uloženie recenzie
        try {
            $review = new Review();
            $review->user_id = $userId;
            $review->accommodation_id = $accommodationId;
            $review->hodnotenie = $hodnotenie;
            $review->komentar = htmlspecialchars($komentar);
            $review->created_at = date('Y-m-d H:i:s');
            $review->save();

            // Získanie mena používateľa pre odpoveď
            $user = User::getOne($userId);

            return new JsonResponse([
                'success' => true,
                'message' => 'Recenzia bola úspešne pridaná',
                'review' => [
                    'id' => $review->id,
                    'hodnotenie' => $review->hodnotenie,
                    'komentar' => $review->komentar,
                    'user_name' => $user ? $user->meno : 'Používateľ',
                    'created_at' => date('d.m.Y')
                ],
                'newAverage' => $accommodation->getAverageRating(),
                'reviewCount' => count($accommodation->getReviews())
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nastala chyba pri ukladaní recenzie'
            ]);
        }
    }

    /**
     * AJAX API - Vymazanie recenzie (len admin)
     */
    public function deleteReview(Request $request): JsonResponse
    {
        // Kontrola prihlásenia
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nie ste prihlásený'
            ]);
        }

        // Kontrola admin práv
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $currentUser = User::getOne($userId);

        if (!$currentUser || !$currentUser->isAdmin()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nemáte oprávnenie mazať recenzie'
            ]);
        }

        $reviewId = (int)$request->value('review_id');

        if ($reviewId <= 0) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Neplatná recenzia'
            ]);
        }

        $review = Review::getOne($reviewId);

        if (!$review) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Recenzia neexistuje'
            ]);
        }

        try {
            $review->delete();
            return new JsonResponse([
                'success' => true,
                'message' => 'Recenzia bola vymazaná'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nastala chyba pri mazaní recenzie'
            ]);
        }
    }
}
