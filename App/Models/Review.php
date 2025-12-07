<?php

namespace App\Models;

use Framework\Core\Model;

class Review extends Model
{
    protected static ?string $tableName = 'review';
    
    protected ?int $id = null;
    protected ?int $user_id = null;
    protected ?int $accommodation_id = null;
    protected ?int $hodnotenie = null;
    protected ?string $komentar = null;
    protected ?string $created_at = null;

    /**
     * Magic getter pre prístup k protected atribútom
     */
    public function __get($name)
    {
        return $this->$name ?? null;
    }

    /**
     * Magic setter pre nastavenie protected atribútov
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Získať hodnotenia pre ubytovanie
     */
    public static function getByAccommodation(int $accommodationId): array
    {
        return self::getAll("accommodation_id = ?", [$accommodationId], "created_at DESC");
    }

    /**
     * Získať hodnotenia od používateľa
     */
    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "created_at DESC");
    }

    /**
     * Získať používateľa ktorý napísal hodnotenie
     */
    public function getUser(): ?User
    {
        if ($this->user_id) {
            return User::getOne($this->user_id);
        }
        return null;
    }

    /**
     * Získať ubytovanie ktoré bolo hodnotené
     */
    public function getAccommodation(): ?Accommodation
    {
        if ($this->accommodation_id) {
            return Accommodation::getOne($this->accommodation_id);
        }
        return null;
    }

    /**
     * Kontrola či používateľ už hodnotil dané ubytovanie
     */
    public static function hasUserReviewed(int $userId, int $accommodationId): bool
    {
        $reviews = self::getAll(
            "user_id = ? AND accommodation_id = ?",
            [$userId, $accommodationId]
        );
        return count($reviews) > 0;
    }

    /**
     * Validácia hodnotenia (1-5)
     */
    public function validateRating(): bool
    {
        return $this->hodnotenie >= 1 && $this->hodnotenie <= 5;
    }
}
