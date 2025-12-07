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
    
    // Pre vzdialenosť pri načítaní cez accommodation
    protected ?float $vzdialenost_km = null;

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
     * Získať všetky atrakcie
     */
    public static function getAllAttractions(): array
    {
        return self::getAll(orderBy: "id DESC");
    }

    /**
     * Získať atrakcie podľa typu
     */
    public static function getByType(string $typ): array
    {
        return self::getAll("typ = ?", [$typ], "id DESC");
    }

    /**
     * Získať všetky typy atrakcií
     */
    public static function getAllTypes(): array
    {
        $sql = "SELECT DISTINCT typ FROM attraction WHERE typ IS NOT NULL ORDER BY typ";
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute();
        
        $types = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $types[] = $row['typ'];
        }
        
        return $types;
    }

    /**
     * Vyhľadávanie atrakcií
     */
    public static function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        return self::getAll(
            "nazov LIKE ? OR popis LIKE ? OR poloha LIKE ?",
            [$searchTerm, $searchTerm, $searchTerm],
            "id DESC"
        );
    }

    /**
     * Získať atrakcie v okolí (podľa polohy)
     */
    public static function getByLocation(string $poloha): array
    {
        return self::getAll("poloha LIKE ?", ['%' . $poloha . '%'], "nazov");
    }

    /**
     * Získať bezplatné atrakcie
     */
    public static function getFreeAttractions(): array
    {
        return self::getAll("cena = ?", [0], "nazov");
    }

    /**
     * Získať platené atrakcie
     */
    public static function getPaidAttractions(): array
    {
        return self::getAll("cena > ?", [0], "cena");
    }

    /**
     * Získať ubytovania v blízkosti tejto atrakcie
     */
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
            $accommodations[] = $accommodation;
        }
        
        return $accommodations;
    }

    /**
     * Kontrola či je atrakcia bezplatná
     */
    public function isFree(): bool
    {
        return $this->cena === 0 || $this->cena === null;
    }

    /**
     * Formátovaná cena
     */
    public function getFormattedPrice(): string
    {
        if ($this->isFree()) {
            return 'Zdarma';
        }
        return $this->cena . ' €';
    }
}
