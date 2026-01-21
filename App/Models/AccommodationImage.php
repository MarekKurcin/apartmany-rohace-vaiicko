<?php

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;

class AccommodationImage extends Model
{
    protected static ?string $tableName = 'accommodation_image';

    protected ?int $id = null;
    protected ?int $accommodation_id = null;
    protected ?string $image_path = null;
    protected ?bool $is_primary = false;
    protected ?int $sort_order = 0;
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
        return self::getAll(
            "accommodation_id = ?",
            [$accommodationId],
            "is_primary DESC, sort_order ASC, id ASC"
        );
    }

    public static function getPrimary(int $accommodationId): ?self
    {
        $images = self::getAll(
            "accommodation_id = ? AND is_primary = 1",
            [$accommodationId],
            "id ASC",
            1
        );
        return $images[0] ?? null;
    }

    public function setPrimary(): bool
    {
        $sql = "UPDATE accommodation_image SET is_primary = 0 WHERE accommodation_id = ?";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->accommodation_id]);

        $this->is_primary = true;
        return $this->save();
    }

    public function deleteWithFile(): bool
    {
        if ($this->image_path && strpos($this->image_path, '/uploads/') === 0) {
            $fullPath = __DIR__ . '/../../public' . $this->image_path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        return $this->delete();
    }

    public static function countByAccommodation(int $accommodationId): int
    {
        $sql = "SELECT COUNT(*) as count FROM accommodation_image WHERE accommodation_id = ?";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$accommodationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
}
