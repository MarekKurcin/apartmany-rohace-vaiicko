<?php

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;
use PDO;

class Accommodation extends Model
{
    protected static ?string $tableName = 'accommodation';

    protected ?int $id = null;
    protected ?int $user_id = null;
    protected ?string $nazov = null;
    protected ?string $popis = null;
    protected ?string $adresa = null;
    protected ?int $kapacita = null;
    protected ?float $cena_za_noc = null;
    protected ?string $vybavenie = null;
    protected ?string $obrazok = null;
    protected ?bool $aktivne = true;

    public ?float $vzdialenost_km = null;

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public static function getAllActive(): array
    {
        return self::getAll("aktivne = ?", [1], "id DESC");
    }

    public static function getAllForAdmin(): array
    {
        return self::getAll(orderBy: "id DESC");
    }

    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "id DESC");
    }

    public static function search(array $filters = []): array
    {
        $where = ["aktivne = ?"];
        $params = [1];

        if (!empty($filters['kapacita'])) {
            $where[] = "kapacita >= ?";
            $params[] = (int)$filters['kapacita'];
        }

        if (!empty($filters['max_cena'])) {
            $where[] = "cena_za_noc <= ?";
            $params[] = (float)$filters['max_cena'];
        }

        if (!empty($filters['vybavenie'])) {
            $vybavenieItems = array_map('trim', explode(',', $filters['vybavenie']));
            foreach ($vybavenieItems as $item) {
                if (!empty($item)) {
                    $where[] = "vybavenie LIKE ?";
                    $params[] = '%' . $item . '%';
                }
            }
        }

        $orderBy = "id DESC";
        if (!empty($filters['zoradenie'])) {
            switch ($filters['zoradenie']) {
                case 'cena_asc':
                    $orderBy = "cena_za_noc ASC";
                    break;
                case 'cena_desc':
                    $orderBy = "cena_za_noc DESC";
                    break;
                case 'kapacita_asc':
                    $orderBy = "kapacita ASC";
                    break;
                case 'kapacita_desc':
                    $orderBy = "kapacita DESC";
                    break;
                case 'najnovsie':
                default:
                    $orderBy = "id DESC";
                    break;
            }
        }

        $whereString = implode(" AND ", $where);
        return self::getAll($whereString, $params, $orderBy);
    }

    public function toggleActive(): bool
    {
        $this->aktivne = !$this->aktivne;
        return $this->save();
    }

    public function getOwner(): ?User
    {
        if ($this->user_id) {
            return User::getOne($this->user_id);
        }
        return null;
    }

    public function getAttractions(): array
    {
        $sql = "SELECT a.*, aa.vzdialenost_km
                FROM attraction a
                JOIN accommodation_attraction aa ON a.id = aa.attraction_id
                WHERE aa.accommodation_id = ?
                ORDER BY aa.vzdialenost_km";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->id]);

        $attractions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $attraction = new Attraction();
            foreach ($row as $key => $value) {
                if (property_exists($attraction, $key)) {
                    $attraction->$key = $value;
                }
            }
            $attraction->vzdialenost_km = $row['vzdialenost_km'];
            $attractions[] = $attraction;
        }

        return $attractions;
    }

    public function attachAttraction(int $attractionId, float $vzdialenostKm): bool
    {
        $sql = "INSERT INTO accommodation_attraction (accommodation_id, attraction_id, vzdialenost_km)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE vzdialenost_km = ?";

        $stmt = Connection::getInstance()->prepare($sql);
        return $stmt->execute([$this->id, $attractionId, $vzdialenostKm, $vzdialenostKm]);
    }

    public function detachAttraction(int $attractionId): bool
    {
        $sql = "DELETE FROM accommodation_attraction
                WHERE accommodation_id = ? AND attraction_id = ?";

        $stmt = Connection::getInstance()->prepare($sql);
        return $stmt->execute([$this->id, $attractionId]);
    }

    public function getReviews(): array
    {
        return Review::getAll("accommodation_id = ?", [$this->id], "created_at DESC");
    }

    public function getAverageRating(): ?float
    {
        $sql = "SELECT AVG(hodnotenie) as avg_rating FROM review WHERE accommodation_id = ?";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['avg_rating'] ? round((float)$result['avg_rating'], 1) : null;
    }

    public function getVybavenieArray(): array
    {
        if (!$this->vybavenie) {
            return [];
        }
        return array_map('trim', explode(',', $this->vybavenie));
    }

    public function getImages(): array
    {
        try {
            return AccommodationImage::getByAccommodation($this->id);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPrimaryImage(): ?string
    {
        try {
            $primary = AccommodationImage::getPrimary($this->id);
            if ($primary) {
                return $primary->image_path;
            }
        } catch (\Exception $e) {
        }
        return $this->obrazok;
    }

    public function getAllImages(): array
    {
        $images = [];

        if ($this->obrazok) {
            $images[] = $this->obrazok;
        }

        try {
            foreach ($this->getImages() as $img) {
                if ($img->image_path && !in_array($img->image_path, $images)) {
                    $images[] = $img->image_path;
                }
            }
        } catch (\Exception $e) {
        }

        return $images;
    }
}
