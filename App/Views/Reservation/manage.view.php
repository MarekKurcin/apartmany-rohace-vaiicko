<?php
$mesiacNazvy = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Máj', 6 => 'Jún', 7 => 'Júl', 8 => 'Aug',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec'
];

$cakajuce = 0;
$potvrdene = 0;
$dokoncene = 0;
$zrusene = 0;
$celkovyPrijem = 0;
$prijemTentoMesiac = 0;
$currentMonth = (int)date('m');

foreach ($reservations as $r) {
    if ($r->stav === 'cakajuca') $cakajuce++;
    if ($r->stav === 'potvrdena') $potvrdene++;
    if ($r->stav === 'dokoncena') $dokoncene++;
    if ($r->stav === 'zrusena') $zrusene++;
    if (in_array($r->stav, ['potvrdena', 'dokoncena'])) {
        $celkovyPrijem += $r->celkova_cena;
        if (date('m', strtotime($r->datum_od)) == $currentMonth) {
            $prijemTentoMesiac += $r->celkova_cena;
        }
    }
}

$prijmyPoMesiacoch = [];
$rezervaciePoMesiacoch = [];
foreach ($monthlyStats as $stat) {
    $prijmyPoMesiacoch[] = $stat['prijem'];
    $rezervaciePoMesiacoch[] = $stat['pocet_rezervacii'];
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-calendar-week"></i> Sprava rezervacii</h1>
        <div>
            <a href="<?= $link->url('accommodation.myList') ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-house"></i> Moje ubytovania
            </a>
            <a href="<?= $link->url('reservation.exportCsv') ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 'confirmed':
                    echo 'Rezervácia bola potvrdená.';
                    break;
                case 'rejected':
                    echo 'Rezervácia bola zamietnutá.';
                    break;
                default:
                    echo 'Operácia prebehla úspešne.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['error']) {
                case 'not_found':
                    echo 'Rezervácia nebola nájdená.';
                    break;
                case 'unauthorized':
                    echo 'Nemáte oprávnenie na túto akciu.';
                    break;
                case 'invalid_status':
                    echo 'Túto operáciu nie je možné vykonať.';
                    break;
                default:
                    echo 'Nastala chyba.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4 g-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="display-5 fw-bold text-warning mb-2"><?= $cakajuce ?></div>
                    <div class="text-muted"><i class="bi bi-hourglass-split"></i> Čakajúce</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="display-5 fw-bold text-success mb-2"><?= $potvrdene ?></div>
                    <div class="text-muted"><i class="bi bi-check-circle"></i> Potvrdené</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="display-5 fw-bold mb-2" style="color: var(--primary-color)"><?= $occupancy ?>%</div>
                    <div class="text-muted"><i class="bi bi-pie-chart"></i> Obsadenosť (<?= $mesiacNazvy[$currentMonth] ?>)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="display-5 fw-bold mb-2" style="color: var(--gold-color)"><?= number_format($celkovyPrijem, 0, ',', ' ') ?>&euro;</div>
                    <div class="text-muted"><i class="bi bi-cash-stack"></i> Celkový príjem</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Prijmy v roku <?= $currentYear ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Stav rezervácií</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($accommodationStats)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="bi bi-house"></i> Statistiky podla ubytovani</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ubytovanie</th>
                            <th class="text-center">Celkom</th>
                            <th class="text-center">Čakajúce</th>
                            <th class="text-center">Potvrdené</th>
                            <th class="text-center">Dokončené</th>
                            <th class="text-center">Priem. dĺžka</th>
                            <th class="text-end">Príjem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accommodationStats as $accId => $stats): ?>
                        <tr>
                            <td>
                                <a href="<?= $link->url('accommodation.show', ['id' => $accId]) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($stats['nazov']) ?>
                                </a>
                            </td>
                            <td class="text-center"><?= $stats['celkom'] ?></td>
                            <td class="text-center">
                                <?php if ($stats['cakajuce'] > 0): ?>
                                    <span class="badge bg-warning"><?= $stats['cakajuce'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($stats['potvrdene'] > 0): ?>
                                    <span class="badge bg-success"><?= $stats['potvrdene'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $stats['dokoncene'] ?></td>
                            <td class="text-center"><?= $stats['priemerna_dlzka'] ?> nocí</td>
                            <td class="text-end fw-bold"><?= number_format($stats['celkovy_prijem'], 0, ',', ' ') ?> &euro;</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Zoznam rezervacii</h5>
            <div class="d-flex gap-2">
                <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Všetky stavy</option>
                    <option value="cakajuca">Čakajúce</option>
                    <option value="potvrdena">Potvrdené</option>
                    <option value="dokoncena">Dokončené</option>
                    <option value="zrusena">Zrušené</option>
                </select>
                <select id="filterAccommodation" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Všetky ubytovania</option>
                    <?php foreach ($accommodations as $acc): ?>
                        <option value="<?= $acc->id ?>"><?= htmlspecialchars($acc->nazov) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($reservations)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-3">Zatiaľ nemáte žiadne rezervácie.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="reservationsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Ubytovanie</th>
                                <th>Hosť</th>
                                <th>Termín</th>
                                <th>Osôb</th>
                                <th>Cena</th>
                                <th>Stav</th>
                                <th>Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <?php
                                $accommodation = $reservation->getAccommodation();
                                $guest = $reservation->getUser();
                                ?>
                                <tr class="reservation-row <?= $reservation->stav === 'cakajuca' ? 'table-warning' : '' ?>"
                                    data-status="<?= $reservation->stav ?>"
                                    data-accommodation="<?= $accommodation ? $accommodation->id : '' ?>">
                                    <td>#<?= $reservation->id ?></td>
                                    <td>
                                        <?php if ($accommodation): ?>
                                            <?= htmlspecialchars($accommodation->nazov) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($guest): ?>
                                            <strong><?= htmlspecialchars($guest->getFullName()) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($guest->email) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d.m.Y', strtotime($reservation->datum_od)) ?>
                                        -
                                        <?= date('d.m.Y', strtotime($reservation->datum_do)) ?>
                                        <br><small class="text-muted"><?= $reservation->getNightsCount() ?> nocí</small>
                                    </td>
                                    <td><?= $reservation->pocet_osob ?></td>
                                    <td><strong><?= number_format($reservation->celkova_cena, 2, ',', ' ') ?> &euro;</strong></td>
                                    <td>
                                        <span class="badge bg-<?= $reservation->getStatusColor() ?>">
                                            <?= $reservation->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $link->url('reservation.show', ['id' => $reservation->id]) ?>"
                                               class="btn btn-outline-secondary" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($reservation->stav === 'cakajuca'): ?>
                                                <a href="<?= $link->url('reservation.confirm', ['id' => $reservation->id]) ?>"
                                                   class="btn btn-success" title="Potvrdiť"
                                                   onclick="return confirm('Potvrdiť túto rezerváciu?')">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                                <a href="<?= $link->url('reservation.reject', ['id' => $reservation->id]) ?>"
                                                   class="btn btn-danger" title="Zamietnuť"
                                                   onclick="return confirm('Zamietnuť túto rezerváciu?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_values($mesiacNazvy)) ?>,
                datasets: [{
                    label: 'Príjem (€)',
                    data: <?= json_encode($prijmyPoMesiacoch) ?>,
                    backgroundColor: 'rgba(45, 106, 79, 0.7)',
                    borderColor: 'rgba(45, 106, 79, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        }
                    }
                }
            }
        });
    }

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Čakajúce', 'Potvrdené', 'Dokončené', 'Zrušené'],
                datasets: [{
                    data: [<?= $cakajuce ?>, <?= $potvrdene ?>, <?= $dokoncene ?>, <?= $zrusene ?>],
                    backgroundColor: [
                        '#ffc107',
                        '#198754',
                        '#0dcaf0',
                        '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    const filterStatus = document.getElementById('filterStatus');
    const filterAccommodation = document.getElementById('filterAccommodation');
    const rows = document.querySelectorAll('.reservation-row');

    function filterTable() {
        const statusValue = filterStatus.value;
        const accValue = filterAccommodation.value;

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowAcc = row.dataset.accommodation;

            let showByStatus = !statusValue || rowStatus === statusValue;
            let showByAcc = !accValue || rowAcc === accValue;

            row.style.display = (showByStatus && showByAcc) ? '' : 'none';
        });
    }

    if (filterStatus) filterStatus.addEventListener('change', filterTable);
    if (filterAccommodation) filterAccommodation.addEventListener('change', filterTable);
});
</script>
