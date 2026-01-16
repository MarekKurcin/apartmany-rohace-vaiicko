<?php

namespace App\Controllers;

use App\Models\User;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class AuthController
 * Kontrolér pre autentifikáciu používateľov
 */
class AuthController extends BaseController
{
    /**
     * Autorizácia - všetky akcie sú verejné
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Index action - redirect to login
     */
    public function index(Request $request): Response
    {
        return $this->redirect($this->url('auth.login'));
    }

    /**
     * Zobrazenie prihlasovacieho formulára
     */
    public function login(Request $request): Response
    {
        if ($this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('home.index'));
        }

        return $this->html();
    }

    /**
     * Spracovanie prihlásenia
     */
    public function loginPost(Request $request): Response
    {
        $email = trim($request->value('email') ?? '');
        $heslo = $request->value('heslo') ?? '';

        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Zadajte platný email';
        }

        if (empty($heslo)) {
            $errors['heslo'] = 'Zadajte heslo';
        }

        if (!empty($errors)) {
            return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
        }

        // Try to authenticate using the framework's authenticator
        if ($this->app->getAuthenticator()->login($email, $heslo)) {
            return $this->redirect($this->url('home.index'));
        } else {
            return $this->html([
                'errors' => ['login' => 'Nesprávny email alebo heslo'],
                'old' => ['email' => $email]
            ], 'login');
        }
    }

    /**
     * Zobrazenie registračného formulára
     */
    public function register(Request $request): Response
    {
        if ($this->app->getAuthenticator()->getUser()->isLoggedIn()) {
            return $this->redirect($this->url('home.index'));
        }

        return $this->html();
    }

    /**
     * Spracovanie registrácie
     */
    public function registerPost(Request $request): Response
    {
        $errors = $this->validateRegistration($request);

        if (!empty($errors)) {
            return $this->html(['errors' => $errors, 'old' => $request->post()], 'register');
        }

        // Kontrola či email už existuje
        if (User::emailExists($request->value('email'))) {
            return $this->html([
                'errors' => ['email' => 'Tento email je už registrovaný'],
                'old' => $request->post()
            ], 'register');
        }

        $user = new User();
        $data = [
            'email' => trim($request->value('email')),
            'heslo' => $request->value('heslo'),
            'meno' => htmlspecialchars(trim($request->value('meno'))),
            'priezvisko' => htmlspecialchars(trim($request->value('priezvisko'))),
            'telefon' => htmlspecialchars(trim($request->value('telefon') ?? '')),
            'rola' => 'turista'
        ];

        try {
            $user->register($data);
            return $this->redirect($this->url('auth.login', ['success' => 'registered']));
        } catch (\Exception $e) {
            return $this->html([
                'errors' => ['register' => 'Registrácia zlyhala, skúste to znova'],
                'old' => $request->post()
            ], 'register');
        }
    }

    /**
     * Odhlásenie
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuthenticator()->logout();
        return $this->redirect($this->url('home.index', ['success' => 'logged_out']));
    }

    /**
     * Validácia registračných údajov
     */
    private function validateRegistration(Request $request): array
    {
        $errors = [];

        $email = trim($request->value('email') ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Zadajte platný email';
        }

        $heslo = $request->value('heslo') ?? '';
        if (empty($heslo) || strlen($heslo) < 6) {
            $errors['heslo'] = 'Heslo musí mať minimálne 6 znakov';
        }

        $heslo_potvrdenie = $request->value('heslo_potvrdenie') ?? '';
        if ($heslo !== $heslo_potvrdenie) {
            $errors['heslo_potvrdenie'] = 'Heslá sa nezhodujú';
        }

        $meno = trim($request->value('meno') ?? '');
        if (empty($meno) || strlen($meno) < 2) {
            $errors['meno'] = 'Meno musí mať minimálne 2 znaky';
        }

        $priezvisko = trim($request->value('priezvisko') ?? '');
        if (empty($priezvisko) || strlen($priezvisko) < 2) {
            $errors['priezvisko'] = 'Priezvisko musí mať minimálne 2 znaky';
        }

        return $errors;
    }
}
