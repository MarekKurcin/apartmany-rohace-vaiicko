<?php

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;

class Attraction extends Model
{
    protected static ?string $tableName = 'attraction';

    protected ?int $id = null;
    protected ?string $nazov = null;
    protected ?string $popis = null;
    protected ?string $typ = null;
    protected ?int $cena = 0;
    protected ?string $poloha = null;
    protected ?string $obrazok = null;

    protected ?float $vzdialenost_km = null;

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public static function getAllAttractions(): array
    {
        return self::getAll(orderBy: "id DESC");
    }

    public static function getByType(string $typ): array
    {
        return self::getAll("typ = ?", [$typ], "id DESC");
    }

    public static function getAllTypes(): array
    {
        $sql = "SELECT DISTINCT typ FROM attraction WHERE typ IS NOT NULL AND typ != '' ORDER BY typ";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute();

        $types = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty(trim($row['typ']))) {
                $types[] = $row['typ'];
            }
        }

        return $types;
    }

    public static function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        return self::getAll(
            "nazov LIKE ? OR popis LIKE ? OR poloha LIKE ?",
            [$searchTerm, $searchTerm, $searchTerm],
            "id DESC"
        );
    }

    public static function searchWithFilters(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['typ'])) {
            $where[] = "typ = ?";
            $params[] = $filters['typ'];
        }

        if (!empty($filters['cena_filter'])) {
            if ($filters['cena_filter'] === 'zadarmo') {
                $where[] = "(cena = 0 OR cena IS NULL)";
            } elseif ($filters['cena_filter'] === 'platene') {
                $where[] = "cena > 0";
            }
        }

        $orderBy = "id DESC";
        if (!empty($filters['zoradenie'])) {
            switch ($filters['zoradenie']) {
                case 'nazov_asc':
                    $orderBy = "nazov ASC";
                    break;
                case 'nazov_desc':
                    $orderBy = "nazov DESC";
                    break;
                case 'cena_asc':
                    $orderBy = "cena ASC";
                    break;
                case 'cena_desc':
                    $orderBy = "cena DESC";
                    break;
                case 'najnovsie':
                default:
                    $orderBy = "id DESC";
                    break;
            }
        }

        $whereString = !empty($where) ? implode(" AND ", $where) : null;
        return self::getAll($whereString, $params, $orderBy);
    }

    public static function getByLocation(string $poloha): array
    {
        return self::getAll("poloha LIKE ?", ['%' . $poloha . '%'], "nazov");
    }

    public static function getFreeAttractions(): array
    {
        return self::getAll("cena = ?", [0], "nazov");
    }

    public static function getPaidAttractions(): array
    {
        return self::getAll("cena > ?", [0], "cena");
    }

    public function getNearbyAccommodations(): array
    {
        $sql = "SELECT a.*, aa.vzdialenost_km
                FROM accommodation a
                JOIN accommodation_attraction aa ON a.id = aa.accommodation_id
                WHERE aa.attraction_id = ? AND a.aktivne = 1
                ORDER BY aa.vzdialenost_km";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->id]);

        $accommodations = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $accommodation = new Accommodation();
            foreach ($row as $key => $value) {
                if (property_exists($accommodation, $key)) {
                    $accommodation->$key = $value;
                }
            }
            $accommodation->vzdialenost_km = $row['vzdialenost_km'];
            $accommodations[] = $accommodation;
        }

        return $accommodations;
    }

    public function isFree(): bool
    {
        return $this->cena === 0 || $this->cena === null;
    }

    public function getFormattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Zdarma';
        }
        return $this->cena . ' €';
    }
}
