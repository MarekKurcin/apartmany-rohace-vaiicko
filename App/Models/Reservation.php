<?php

namespace App\Models;

use Framework\Core\Model;
use Framework\DB\Connection;
use DateTime;

class Reservation extends Model
{
    protected static ?string $tableName = 'reservation';
    
    protected ?int $id = null;
    protected ?int $user_id = null;
    protected ?int $accommodation_id = null;
    protected ?string $datum_od = null;
    protected ?string $datum_do = null;
    protected ?int $pocet_osob = null;
    protected ?float $celkova_cena = null;
    protected ?string $stav = 'cakajuca';

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
     * Získať rezervácie používateľa
     */
    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "datum_od DESC");
    }

    /**
     * Získať rezervácie pre ubytovanie
     */
    public static function getByAccommodation(int $accommodationId): array
    {
        return self::getAll("accommodation_id = ?", [$accommodationId], "datum_od DESC");
    }

    /**
     * Získať aktívne rezervácie
     */
    public static function getActive(): array
    {
        return self::getAll(
            "stav IN ('cakajuca', 'potvrdena') AND datum_do >= CURDATE()",
            [],
            "datum_od"
        );
    }

    /**
     * Kontrola dostupnosti ubytovania v danom období
     */
    public static function isAvailable(int $accommodationId, string $datumOd, string $datumDo, ?int $excludeReservationId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM reservation 
                WHERE accommodation_id = ? 
                AND stav IN ('cakajuca', 'potvrdena')
                AND (
                    (datum_od <= ? AND datum_do >= ?) OR
                    (datum_od <= ? AND datum_do >= ?) OR
                    (datum_od >= ? AND datum_do <= ?)
                )";
        
        $params = [$accommodationId, $datumOd, $datumOd, $datumDo, $datumDo, $datumOd, $datumDo];
        
        if ($excludeReservationId) {
            $sql .= " AND id != ?";
            $params[] = $excludeReservationId;
        }
        
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }

    /**
     * Vypočítať počet nocí
     */
    public function getNightsCount(): int
    {
        if (!$this->datum_od || !$this->datum_do) {
            return 0;
        }
        
        $dateOd = new DateTime($this->datum_od);
        $dateDo = new DateTime($this->datum_do);
        $interval = $dateOd->diff($dateDo);
        
        return $interval->days;
    }

    /**
     * Vypočítať celkovú cenu
     */
    public function calculateTotalPrice(float $cenaZaNoc): float
    {
        return $this->getNightsCount() * $cenaZaNoc;
    }

    /**
     * Potvrdiť rezerváciu
     */
    public function confirm(): bool
    {
        $this->stav = 'potvrdena';
        return $this->save();
    }

    /**
     * Zrušiť rezerváciu
     */
    public function cancel(): bool
    {
        $this->stav = 'zrusena';
        return $this->save();
    }

    /**
     * Označiť ako dokončenú
     */
    public function complete(): bool
    {
        $this->stav = 'dokoncena';
        return $this->save();
    }

    /**
     * Získať používateľa
     */
    public function getUser(): ?User
    {
        if ($this->user_id) {
            return User::getOne($this->user_id);
        }
        return null;
    }

    /**
     * Získať ubytovanie
     */
    public function getAccommodation(): ?Accommodation
    {
        if ($this->accommodation_id) {
            return Accommodation::getOne($this->accommodation_id);
        }
        return null;
    }

    /**
     * Kontrola či je rezervácia aktívna
     */
    public function isActive(): bool
    {
        return in_array($this->stav, ['cakajuca', 'potvrdena']) && 
               $this->datum_do >= date('Y-m-d');
    }

    /**
     * Kontrola či už rezervácia prebehla
     */
    public function isPast(): bool
    {
        return $this->datum_do < date('Y-m-d');
    }

    /**
     * Získať farbu podľa stavu
     */
    public function getStatusColor(): string
    {
        return match($this->stav) {
            'cakajuca' => 'warning',
            'potvrdena' => 'success',
            'zrusena' => 'danger',
            'dokoncena' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Získať preložený stav
     */
    public function getStatusLabel(): string
    {
        return match($this->stav) {
            'cakajuca' => 'Čakajúca',
            'potvrdena' => 'Potvrdená',
            'zrusena' => 'Zrušená',
            'dokoncena' => 'Dokončená',
            default => $this->stav
        };
    }
}
