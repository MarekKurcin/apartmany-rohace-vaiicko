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

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public static function getByUser(int $userId): array
    {
        return self::getAll("user_id = ?", [$userId], "datum_od DESC");
    }

    public static function getByAccommodation(int $accommodationId): array
    {
        return self::getAll("accommodation_id = ?", [$accommodationId], "datum_od DESC");
    }

    public static function getActive(): array
    {
        return self::getAll(
            "stav IN ('cakajuca', 'potvrdena') AND datum_do >= CURDATE()",
            [],
            "datum_od"
        );
    }

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

    public function calculateTotalPrice(float $cenaZaNoc): float
    {
        return $this->getNightsCount() * $cenaZaNoc;
    }

    public function confirm(): void
    {
        $this->stav = 'potvrdena';
        $this->save();
    }

    public function cancel(): void
    {
        $this->stav = 'zrusena';
        $this->save();
    }

    public function complete(): void
    {
        $this->stav = 'dokoncena';
        $this->save();
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

    public function isActive(): bool
    {
        return in_array($this->stav, ['cakajuca', 'potvrdena']) &&
               $this->datum_do >= date('Y-m-d');
    }

    public function isPast(): bool
    {
        return $this->datum_do < date('Y-m-d');
    }

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

    public static function getMonthlyStats(array $accommodationIds, int $year): array
    {
        if (empty($accommodationIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($accommodationIds), '?'));
        $params = array_merge($accommodationIds, [$year]);

        $sql = "SELECT
                    MONTH(datum_od) as mesiac,
                    COUNT(*) as pocet_rezervacii,
                    SUM(CASE WHEN stav IN ('potvrdena', 'dokoncena') THEN celkova_cena ELSE 0 END) as prijem,
                    SUM(CASE WHEN stav = 'potvrdena' THEN 1 ELSE 0 END) as potvrdene,
                    SUM(CASE WHEN stav = 'cakajuca' THEN 1 ELSE 0 END) as cakajuce,
                    SUM(CASE WHEN stav = 'zrusena' THEN 1 ELSE 0 END) as zrusene,
                    SUM(CASE WHEN stav = 'dokoncena' THEN 1 ELSE 0 END) as dokoncene
                FROM reservation
                WHERE accommodation_id IN ($placeholders)
                AND YEAR(datum_od) = ?
                GROUP BY MONTH(datum_od)
                ORDER BY mesiac";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stats = [];
        for ($i = 1; $i <= 12; $i++) {
            $stats[$i] = [
                'mesiac' => $i,
                'pocet_rezervacii' => 0,
                'prijem' => 0,
                'potvrdene' => 0,
                'cakajuce' => 0,
                'zrusene' => 0,
                'dokoncene' => 0
            ];
        }

        foreach ($results as $row) {
            $stats[(int)$row['mesiac']] = [
                'mesiac' => (int)$row['mesiac'],
                'pocet_rezervacii' => (int)$row['pocet_rezervacii'],
                'prijem' => (float)$row['prijem'],
                'potvrdene' => (int)$row['potvrdene'],
                'cakajuce' => (int)$row['cakajuce'],
                'zrusene' => (int)$row['zrusene'],
                'dokoncene' => (int)$row['dokoncene']
            ];
        }

        return $stats;
    }

    public static function getOccupancyForMonth(array $accommodationIds, int $year, int $month): float
    {
        if (empty($accommodationIds)) {
            return 0.0;
        }

        $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
        $totalPossibleDays = $daysInMonth * count($accommodationIds);

        $firstDay = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $placeholders = implode(',', array_fill(0, count($accommodationIds), '?'));
        $params = array_merge($accommodationIds, [$lastDay, $firstDay]);

        $sql = "SELECT
                    SUM(
                        DATEDIFF(
                            LEAST(datum_do, '$lastDay'),
                            GREATEST(datum_od, '$firstDay')
                        )
                    ) as obsadene_dni
                FROM reservation
                WHERE accommodation_id IN ($placeholders)
                AND stav IN ('potvrdena', 'dokoncena')
                AND datum_od <= ?
                AND datum_do >= ?";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $occupiedDays = (int)($result['obsadene_dni'] ?? 0);

        if ($totalPossibleDays <= 0) {
            return 0.0;
        }

        return min(100, round(($occupiedDays / $totalPossibleDays) * 100, 1));
    }

    public static function getBookedDates(int $accommodationId, int $year, int $month): array
    {
        $firstDay = sprintf('%04d-%02d-01', $year, $month);
        $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
        $lastDay = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $sql = "SELECT datum_od, datum_do, stav
                FROM reservation
                WHERE accommodation_id = ?
                AND stav IN ('cakajuca', 'potvrdena')
                AND datum_od <= ?
                AND datum_do >= ?";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$accommodationId, $lastDay, $firstDay]);
        $reservations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $bookedDates = [];

        foreach ($reservations as $res) {
            $start = new DateTime(max($res['datum_od'], $firstDay));
            $end = new DateTime(min($res['datum_do'], $lastDay));

            while ($start <= $end) {
                $dateStr = $start->format('Y-m-d');
                $bookedDates[$dateStr] = $res['stav'];
                $start->modify('+1 day');
            }
        }

        return $bookedDates;
    }

    public static function getAccommodationStats(int $accommodationId): array
    {
        $sql = "SELECT
                    COUNT(*) as celkom,
                    SUM(CASE WHEN stav = 'potvrdena' THEN 1 ELSE 0 END) as potvrdene,
                    SUM(CASE WHEN stav = 'cakajuca' THEN 1 ELSE 0 END) as cakajuce,
                    SUM(CASE WHEN stav = 'dokoncena' THEN 1 ELSE 0 END) as dokoncene,
                    SUM(CASE WHEN stav = 'zrusena' THEN 1 ELSE 0 END) as zrusene,
                    SUM(CASE WHEN stav IN ('potvrdena', 'dokoncena') THEN celkova_cena ELSE 0 END) as celkovy_prijem,
                    AVG(CASE WHEN stav IN ('potvrdena', 'dokoncena') THEN DATEDIFF(datum_do, datum_od) END) as priemerna_dlzka
                FROM reservation
                WHERE accommodation_id = ?";

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute([$accommodationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'celkom' => (int)($result['celkom'] ?? 0),
            'potvrdene' => (int)($result['potvrdene'] ?? 0),
            'cakajuce' => (int)($result['cakajuce'] ?? 0),
            'dokoncene' => (int)($result['dokoncene'] ?? 0),
            'zrusene' => (int)($result['zrusene'] ?? 0),
            'celkovy_prijem' => (float)($result['celkovy_prijem'] ?? 0),
            'priemerna_dlzka' => round((float)($result['priemerna_dlzka'] ?? 0), 1)
        ];
    }
}
