<?php
/** @var array $reservations */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <h1 class="mb-4"><i class="bi bi-calendar-week"></i> Správa rezervácií</h1>

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

    <!-- Štatistiky -->
    <?php
    $cakajuce = 0;
    $potvrdene = 0;
    $celkovyPrijem = 0;
    foreach ($reservations as $r) {
        if ($r->stav === 'cakajuca') $cakajuce++;
        if ($r->stav === 'potvrdena') $potvrdene++;
        if (in_array($r->stav, ['potvrdena', 'dokoncena'])) {
            $celkovyPrijem += $r->celkova_cena;
        }
    }
    ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3><?= $cakajuce ?></h3>
                    <p class="mb-0">Čakajúce na potvrdenie</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $potvrdene ?></h3>
                    <p class="mb-0">Potvrdené rezervácie</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= number_format($celkovyPrijem, 0, ',', ' ') ?> &euro;</h3>
                    <p class="mb-0">Celkový príjem</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Zatiaľ nemáte žiadne rezervácie.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
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
                        <tr class="<?= $reservation->stav === 'cakajuca' ? 'table-warning' : '' ?>">
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
                                       class="btn btn-outline-primary" title="Detail">
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
