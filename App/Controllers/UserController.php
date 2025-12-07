<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\User;

class UserController extends BaseController
{
    /**
     * Autorizácia - všetky akcie vyžadujú prihlásenie
     */
    public function authorize(Request $request, string $action): bool
    {
        return $this->app->getAuthenticator()->getUser()->isLoggedIn();
    }

    /**
     * Zobrazenie profilu používateľa
     */
    public function index(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $currentUser = User::getOne($userId);
        
        if (!$currentUser) {
            return $this->redirect($this->url('auth.login'));
        }

        return $this->html(['currentUser' => $currentUser]);
    }

    /**
     * Alias pre index - zobrazenie profilu
     */
    public function profile(Request $request): Response
    {
        return $this->index($request);
    }

    /**
     * Zobrazenie formulára pre úpravu profilu
     */
    public function edit(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $currentUser = User::getOne($userId);
        
        if (!$currentUser) {
            return $this->redirect($this->url('auth.login'));
        }

        return $this->html(['currentUser' => $currentUser]);
    }

    /**
     * Aktualizácia profilu
     */
    public function update(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $currentUser = User::getOne($userId);
        
        if (!$currentUser) {
            return $this->redirect($this->url('auth.login'));
        }

        $errors = $this->validate($request, $currentUser);

        if (!empty($errors)) {
            return $this->html([
                'errors' => $errors,
                'currentUser' => $currentUser,
                'old' => $request->post()
            ], viewName: 'edit');
        }

        $currentUser->meno = htmlspecialchars(trim($request->value('meno')));
        $currentUser->priezvisko = htmlspecialchars(trim($request->value('priezvisko')));
        $currentUser->telefon = htmlspecialchars(trim($request->value('telefon')));
        
        // Email môže byť upravený len ak nie je duplicitný
        $newEmail = trim($request->value('email'));
        if ($newEmail !== $currentUser->email) {
            if (User::emailExists($newEmail)) {
                return $this->html([
                    'errors' => ['email' => 'Email už existuje'],
                    'currentUser' => $currentUser,
                    'old' => $request->post()
                ], viewName: 'edit');
            }
            $currentUser->email = htmlspecialchars($newEmail);
        }

        if ($currentUser->save()) {
            return $this->redirect($this->url('user.profile', ['success' => 'updated']));
        } else {
            return $this->redirect($this->url('user.edit', ['error' => 'failed']));
        }
    }

    /**
     * Zmena hesla
     */
    public function changePassword(Request $request): Response
    {
        $userId = $this->app->getAuthenticator()->getUser()->getId();
        $currentUser = User::getOne($userId);
        
        if (!$currentUser) {
            return $this->redirect($this->url('auth.login'));
        }

        if ($request->post()) {
            $currentPassword = $request->value('current_password');
            $newPassword = $request->value('new_password');
            $confirmPassword = $request->value('confirm_password');

            $errors = [];

            if (empty($currentPassword)) {
                $errors['current_password'] = 'Zadajte súčasné heslo';
            } elseif (!password_verify($currentPassword, $currentUser->heslo)) {
                $errors['current_password'] = 'Nesprávne súčasné heslo';
            }

            if (empty($newPassword) || strlen($newPassword) < 6) {
                $errors['new_password'] = 'Nové heslo musí mať minimálne 6 znakov';
            }

            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Heslá sa nezhodujú';
            }

            if (empty($errors)) {
                $currentUser->heslo = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($currentUser->save()) {
                    return $this->redirect($this->url('user.profile', ['success' => 'password_changed']));
                } else {
                    $errors['general'] = 'Nepodarilo sa zmeniť heslo';
                }
            }

            return $this->html([
                'errors' => $errors,
                'currentUser' => $currentUser
            ]);
        }

        return $this->html(['currentUser' => $currentUser]);
    }

    /**
     * Validácia dát profilu
     */
    private function validate(Request $request, User $user): array
    {
        $errors = [];

        $meno = trim($request->value('meno'));
        if (empty($meno) || strlen($meno) < 2) {
            $errors['meno'] = 'Meno musí mať minimálne 2 znaky';
        }

        $priezvisko = trim($request->value('priezvisko'));
        if (empty($priezvisko) || strlen($priezvisko) < 2) {
            $errors['priezvisko'] = 'Priezvisko musí mať minimálne 2 znaky';
        }

        $email = trim($request->value('email'));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Neplatný email';
        }

        $telefon = trim($request->value('telefon'));
        if (!empty($telefon) && !preg_match('/^\+?[0-9]{9,15}$/', $telefon)) {
            $errors['telefon'] = 'Neplatné telefónne číslo';
        }

        return $errors;
    }
}
