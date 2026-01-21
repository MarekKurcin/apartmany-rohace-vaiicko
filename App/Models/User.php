<?php

namespace App\Models;

use Framework\Core\Model;
use PDO;

class User extends Model
{
    protected static ?string $tableName = 'users';

    protected ?int $id = null;
    protected ?string $email = null;
    protected ?string $heslo = null;
    protected ?string $meno = null;
    protected ?string $priezvisko = null;
    protected ?string $telefon = null;
    protected ?string $rola = 'turista';
    protected ?string $datum_vytvorenia = null;

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function register(array $data): void
    {
        $this->email = $data['email'];
        $this->heslo = password_hash($data['heslo'], PASSWORD_DEFAULT);
        $this->meno = $data['meno'];
        $this->priezvisko = $data['priezvisko'];
        $this->telefon = $data['telefon'] ?? null;
        $this->rola = $data['rola'] ?? 'turista';

        $this->save();
    }

    public static function login(string $email, string $heslo): ?User
    {
        $users = self::getAll("email = ?", [$email]);

        if (count($users) > 0) {
            $user = $users[0];
            if (password_verify($heslo, $user->heslo)) {
                return $user;
            }
        }

        return null;
    }

    public static function emailExists(string $email): bool
    {
        $users = self::getAll("email = ?", [$email]);
        return count($users) > 0;
    }

    public function updateProfile(array $data): void
    {
        $this->email = $data['email'];
        $this->meno = $data['meno'];
        $this->priezvisko = $data['priezvisko'];
        $this->telefon = $data['telefon'] ?? null;

        $this->save();
    }

    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!password_verify($currentPassword, $this->heslo)) {
            return false;
        }

        $this->heslo = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->save();
        return true;
    }

    public static function getAllUsers(): array
    {
        return self::getAll();
    }

    public static function getByRole(string $rola): array
    {
        return self::getAll("rola = ?", [$rola]);
    }

    public function updateRole(string $newRole): void
    {
        $this->rola = $newRole;
        $this->save();
    }

    public function getFullName(): string
    {
        return trim(($this->meno ?? '') . ' ' . ($this->priezvisko ?? ''));
    }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'meno' => $this->meno,
            'priezvisko' => $this->priezvisko,
            'telefon' => $this->telefon,
            'rola' => $this->rola,
            'datum_vytvorenia' => $this->datum_vytvorenia
        ];
    }

    public function isAdmin(): bool
    {
        return $this->rola === 'admin';
    }

    public function isUbytovatel(): bool
    {
        return $this->rola === 'ubytovatel' || $this->rola === 'admin';
    }
}
