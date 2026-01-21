<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\Attraction;

class AttractionController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        if (in_array($action, ['index', 'show', 'filterAjax'])) {
            return true;
        }

        return $this->checkAdmin();
    }

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

    public function filterAjax(Request $request): JsonResponse
    {
        try {
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
                    'popis' => $attr->popis ? mb_substr($attr->popis, 0, 120) . (mb_strlen($attr->popis) > 120 ? '...' : '') : '',
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
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

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

    public function create(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        return $this->html();
    }

    public function store(Request $request): Response
    {
        if (!$this->checkAdmin()) {
            return $this->redirect($this->url('home.index', ['error' => 'unauthorized']));
        }

        $errors = $this->validate($request);

        $imageResult = $this->handleImageUpload($request);
        if ($imageResult['error']) {
            $errors['obrazok'] = $imageResult['error'];
        }

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'old' => $request->post()
            ], viewName: 'create');
        }

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

        $imageResult = $this->handleImageUpload($request);
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

    private function handleImageUpload(Request $request): array
    {
        $uploadedFile = $request->file('obrazok');
        
        if ($uploadedFile === null || $uploadedFile->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        if (!$uploadedFile->isOk()) {
            return ['path' => null, 'error' => $uploadedFile->getErrorMessage() ?? 'Chyba pri nahravani suboru'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($uploadedFile->getFileTempPath());

        if (!in_array($mimeType, $allowedTypes)) {
            return ['path' => null, 'error' => 'Povolene su len JPG, PNG a WebP obrazky'];
        }

        $maxSize = 5 * 1024 * 1024;
        if ($uploadedFile->getSize() > $maxSize) {
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

        if ($uploadedFile->store($destination)) {
            return ['path' => '/uploads/attractions/' . $newFilename, 'error' => null];
        }

        return ['path' => null, 'error' => 'Nepodarilo sa ulozit subor'];
    }

    private function deleteOldImage(?string $imagePath): void
    {
        if ($imagePath && strpos($imagePath, '/uploads/attractions/') === 0) {
            $fullPath = __DIR__ . '/../../public' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    private function checkAdmin(): bool
    {
        if (!$this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return false;
        }

        $user = \App\Models\User::getOne($this->app->getAuthenticator()->getUser()->getId());
        return $user && $user->isAdmin();
    }

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
