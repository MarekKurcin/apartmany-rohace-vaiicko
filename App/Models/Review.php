<?php

namespace App\Models;

use Framework\Core\Model;

class Review extends Model
{
    protected static ?string $tableName = 'review';
    
    public ?int $id = null;
    public ?int $user_id = null;
    public ?int $accommodation_id = null;
    public ?int $hodnotenie = null;
    public ?string $komentar = null;
    public ?string $created_at = null;

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
