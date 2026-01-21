<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\Accommodation;
use App\Models\AccommodationImage;
use App\Models\Review;
use App\Models\User;
use App\Models\Reservation;

class AccommodationController extends BaseController
{
    /**
     * Autorizácia - index a show sú verejné, ostatné vyžadujú prihlásenie
     */
    public function authorize(Request $request, string $action): bool
    {
        // Verejné akcie (vrátane AJAX filtrovania a kalendára)
        if (in_array($action, ['index', 'show', 'filterAjax', 'getAvailability', 'getGalleryImages'])) {
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
        // Spracovanie vybavenia z checkboxov
        $vybavenieArr = $request->value('vybavenie_arr');
        $vybavenie = '';
        if (is_array($vybavenieArr) && !empty($vybavenieArr)) {
            $vybavenie = implode(',', $vybavenieArr);
        } elseif ($request->value('vybavenie')) {
            $vybavenie = $request->value('vybavenie');
        }

        // Získanie filtrov z GET
        $filters = [
            'kapacita' => $request->value('kapacita'),
            'max_cena' => $request->value('max_cena'),
            'vybavenie' => $vybavenie,
            'zoradenie' => $request->value('zoradenie') ?: 'najnovsie'
        ];

        // Vyhľadávanie s filtrami
        $accommodations = Accommodation::search($filters);

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
        try {
            // Spracovanie vybavenia z checkboxov
            $vybavenieArr = $request->value('vybavenie_arr');
            $vybavenie = '';
            if (is_array($vybavenieArr) && !empty($vybavenieArr)) {
                $vybavenie = implode(',', $vybavenieArr);
            } elseif ($request->value('vybavenie')) {
                $vybavenie = $request->value('vybavenie');
            }

            $filters = [
                'kapacita' => $request->value('kapacita'),
                'max_cena' => $request->value('max_cena'),
                'vybavenie' => $vybavenie,
                'zoradenie' => $request->value('zoradenie') ?: 'najnovsie'
            ];

            $accommodations = Accommodation::search($filters);

            $result = [];
            foreach ($accommodations as $acc) {
                // Bezpecne ziskanie obrazka
                $obrazok = 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800';
                try {
                    $primary = $acc->getPrimaryImage();
                    if ($primary) {
                        $obrazok = $primary;
                    } elseif ($acc->obrazok) {
                        $obrazok = $acc->obrazok;
                    }
                } catch (\Exception $imgEx) {
                    // Ignorujeme chybu obrazka
                }

                $result[] = [
                    'id' => $acc->id,
                    'nazov' => $acc->nazov,
                    'popis' => $acc->popis ? mb_substr($acc->popis, 0, 100) . (mb_strlen($acc->popis) > 100 ? '...' : '') : '',
                    'adresa' => $acc->adresa,
                    'kapacita' => $acc->kapacita,
                    'cena_za_noc' => number_format((float)$acc->cena_za_noc, 2),
                    'obrazok' => $obrazok,
                    'vybavenie' => $acc->getVybavenieArray()
                ];
            }

            return new JsonResponse([
                'success' => true,
                'count' => count($result),
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Nastala chyba pri načítavaní dát'
            ]);
        }
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

    /**
     * AJAX API - Získanie obsadených dátumov pre kalendár
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $accommodationId = (int)$request->value('id');
        $year = (int)$request->value('year') ?: (int)date('Y');
        $month = (int)$request->value('month') ?: (int)date('m');

        if ($accommodationId <= 0) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Neplatné ID ubytovania'
            ]);
        }

        $accommodation = Accommodation::getOne($accommodationId);
        if (!$accommodation) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Ubytovanie neexistuje'
            ]);
        }

        $bookedDates = Reservation::getBookedDates($accommodationId, $year, $month);

        return new JsonResponse([
            'success' => true,
            'year' => $year,
            'month' => $month,
            'bookedDates' => $bookedDates
        ]);
    }

    /**
     * Zoznam vlastných ubytovaní pre ubytovateľa
     */
    public function myList(Request $request): Response
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('auth.login'));
        }

        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);

        if (!$user->isUbytovatel()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $accommodations = Accommodation::getAll("user_id = ?", [$userId]);

        // Pridáme štatistiky ku každému ubytovaniu
        $accommodationsWithStats = [];
        foreach ($accommodations as $acc) {
            $stats = Reservation::getAccommodationStats($acc->id);
            $accommodationsWithStats[] = [
                'accommodation' => $acc,
                'stats' => $stats
            ];
        }

        return $this->html([
            'accommodations' => $accommodationsWithStats
        ]);
    }

    /**
     * AJAX - Získať obrázky galérie
     */
    public function getGalleryImages(Request $request): JsonResponse
    {
        $accommodationId = (int)$request->value('id');
        $accommodation = Accommodation::getOne($accommodationId);

        if (!$accommodation) {
            return new JsonResponse(['success' => false, 'error' => 'Ubytovanie neexistuje']);
        }

        $images = $accommodation->getAllImages();

        return new JsonResponse([
            'success' => true,
            'images' => $images
        ]);
    }

    /**
     * AJAX - Upload obrázkov do galérie
     */
    public function uploadGalleryImages(Request $request): JsonResponse
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return new JsonResponse(['success' => false, 'error' => 'Nie ste prihlásený']);
        }

        $accommodationId = (int)$request->value('accommodation_id');
        $accommodation = Accommodation::getOne($accommodationId);

        if (!$accommodation) {
            return new JsonResponse(['success' => false, 'error' => 'Ubytovanie neexistuje']);
        }

        // Kontrola oprávnenia
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);
        if ($accommodation->user_id != $userId && !$user->isAdmin()) {
            return new JsonResponse(['success' => false, 'error' => 'Nemáte oprávnenie']);
        }

        if (!isset($_FILES['gallery_images']) || empty($_FILES['gallery_images']['name'][0])) {
            return new JsonResponse(['success' => false, 'error' => 'Žiadne súbory na nahratie']);
        }

        $uploadedImages = [];
        $files = $_FILES['gallery_images'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        $uploadDir = __DIR__ . '/../../public/uploads/gallery/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $currentCount = AccommodationImage::countByAccommodation($accommodationId);

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($currentCount + count($uploadedImages) >= 10) break; // Max 10 obrázkov

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($files['tmp_name'][$i]);

            if (!in_array($mimeType, $allowedTypes)) continue;
            if ($files['size'][$i] > $maxSize) continue;

            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $extension = $extensions[$mimeType];
            $newFilename = 'gallery_' . $accommodationId . '_' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $image = new AccommodationImage();
                $image->accommodation_id = $accommodationId;
                $image->image_path = '/uploads/gallery/' . $newFilename;
                $image->is_primary = ($currentCount + count($uploadedImages) === 0);
                $image->sort_order = $currentCount + count($uploadedImages);
                $image->save();

                $uploadedImages[] = [
                    'id' => $image->id,
                    'path' => $image->image_path,
                    'is_primary' => $image->is_primary
                ];
            }
        }

        return new JsonResponse([
            'success' => true,
            'uploaded' => count($uploadedImages),
            'images' => $uploadedImages
        ]);
    }

    /**
     * AJAX - Vymazať obrázok z galérie
     */
    public function deleteGalleryImage(Request $request): JsonResponse
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return new JsonResponse(['success' => false, 'error' => 'Nie ste prihlásený']);
        }

        $imageId = (int)$request->value('image_id');
        $image = AccommodationImage::getOne($imageId);

        if (!$image) {
            return new JsonResponse(['success' => false, 'error' => 'Obrázok neexistuje']);
        }

        $accommodation = Accommodation::getOne($image->accommodation_id);
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);

        if ($accommodation->user_id != $userId && !$user->isAdmin()) {
            return new JsonResponse(['success' => false, 'error' => 'Nemáte oprávnenie']);
        }

        $wasPrimary = $image->is_primary;
        $image->deleteWithFile();

        // Ak bol primárny, nastavíme prvý obrázok ako primárny
        if ($wasPrimary) {
            $images = AccommodationImage::getByAccommodation($accommodation->id);
            if (!empty($images)) {
                $images[0]->setPrimary();
            }
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * AJAX - Nastaviť primárny obrázok
     */
    public function setPrimaryImage(Request $request): JsonResponse
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return new JsonResponse(['success' => false, 'error' => 'Nie ste prihlásený']);
        }

        $imageId = (int)$request->value('image_id');
        $image = AccommodationImage::getOne($imageId);

        if (!$image) {
            return new JsonResponse(['success' => false, 'error' => 'Obrázok neexistuje']);
        }

        $accommodation = Accommodation::getOne($image->accommodation_id);
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $user = User::getOne($userId);

        if ($accommodation->user_id != $userId && !$user->isAdmin()) {
            return new JsonResponse(['success' => false, 'error' => 'Nemáte oprávnenie']);
        }

        $image->setPrimary();

        return new JsonResponse(['success' => true]);
    }
}
