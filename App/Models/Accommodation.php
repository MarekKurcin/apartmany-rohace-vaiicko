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
     * Získať všetky aktívne ubytovania
     */
    public static function getAllActive(): array
    {
        return self::getAll("aktivne = ?", [1], "id DESC");
    }

    /**
     * Získať všetky ubytovania vrátane neaktívnych (pre admina)
     */
    public static function getAllForAdmin(): array
    {
        return self::getAll(orderBy: "id DESC");
    }

    /**
     * Získať ubytovania podľa používateľa
     */
    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "id DESC");
    }

    /**
     * Vyhľadávanie s filtrami
     */
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
            // Rozdelíme vybavenie na jednotlivé položky a hľadáme všetky
            $vybavenieItems = array_map('trim', explode(',', $filters['vybavenie']));
            foreach ($vybavenieItems as $item) {
                if (!empty($item)) {
                    $where[] = "vybavenie LIKE ?";
                    $params[] = '%' . $item . '%';
                }
            }
        }

        // Zoradenie
        $orderBy = "id DESC"; // default
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

    /**
     * Prepnúť stav aktívnosti
     */
    public function toggleActive(): bool
    {
        $this->aktivne = !$this->aktivne;
        return $this->save();
    }

    /**
     * Získať vlastníka ubytovania
     */
    public function getOwner(): ?User
    {
        if ($this->user_id) {
            return User::getOne($this->user_id);
        }
        return null;
    }

    /**
     * Získať atrakcie v blízkosti
     */
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

    /**
     * Pripojiť atrakciu k ubytovaniu
     */
    public function attachAttraction(int $attractionId, float $vzdialenostKm): bool
    {
        $sql = "INSERT INTO accommodation_attraction (accommodation_id, attraction_id, vzdialenost_km) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE vzdialenost_km = ?";
        
        $stmt = Connection::getInstance()->prepare($sql);
        return $stmt->execute([$this->id, $attractionId, $vzdialenostKm, $vzdialenostKm]);
    }

    /**
     * Odpojiť atrakciu od ubytovania
     */
    public function detachAttraction(int $attractionId): bool
    {
        $sql = "DELETE FROM accommodation_attraction 
                WHERE accommodation_id = ? AND attraction_id = ?";
        
        $stmt = Connection::getInstance()->prepare($sql);
        return $stmt->execute([$this->id, $attractionId]);
    }

    /**
     * Získať hodnotenia
     */
    public function getReviews(): array
    {
        return Review::getAll("accommodation_id = ?", [$this->id], "created_at DESC");
    }

    /**
     * Získať priemerné hodnotenie
     */
    public function getAverageRating(): ?float
    {
        $sql = "SELECT AVG(hodnotenie) as avg_rating FROM review WHERE accommodation_id = ?";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['avg_rating'] ? round((float)$result['avg_rating'], 1) : null;
    }

    /**
     * Získať vybavenie ako pole
     */
    public function getVybavenieArray(): array
    {
        if (!$this->vybavenie) {
            return [];
        }
        return array_map('trim', explode(',', $this->vybavenie));
    }
}
