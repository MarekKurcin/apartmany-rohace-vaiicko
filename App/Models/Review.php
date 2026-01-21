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

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public static function getByAccommodation(int $accommodationId): array
    {
        return self::getAll("accommodation_id = ?", [$accommodationId], "created_at DESC");
    }

    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "created_at DESC");
    }

    public function getUser(): ?User
    {
        if ($this->user_id) {
            return User::getOne($this->user_id);
        }
        return null;
    }

    public function getAccommodation(): ?Accommodation
    {
        if ($this->accommodation_id) {
            return Accommodation::getOne($this->accommodation_id);
        }
        return null;
    }

    public static function hasUserReviewed(int $userId, int $accommodationId): bool
    {
        $reviews = self::getAll(
            "user_id = ? AND accommodation_id = ?",
            [$userId, $accommodationId]
        );
        return count($reviews) > 0;
    }

    public function validateRating(): bool
    {
        return $this->hodnotenie >= 1 && $this->hodnotenie <= 5;
    }
}
