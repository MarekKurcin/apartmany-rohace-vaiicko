<?php
/** @var array $reservations */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <h1 class="mb-4">Moje rezervácie</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo 'Rezervácia bola úspešne vytvorená. Čaká na potvrdenie.';
                    break;
                case 'cancelled':
                    echo 'Rezervácia bola zrušená.';
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
                case 'cannot_cancel':
                    echo 'Túto rezerváciu nie je možné zrušiť.';
                    break;
                default:
                    echo 'Nastala chyba.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nemáte žiadne rezervácie.
            <a href="<?= $link->url('accommodation.index') ?>">Prezrieť ubytovania</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Ubytovanie</th>
                        <th>Dátum príchodu</th>
                        <th>Dátum odchodu</th>
                        <th>Počet osôb</th>
                        <th>Celková cena</th>
                        <th>Stav</th>
                        <th>Akcie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <?php $accommodation = $reservation->getAccommodation(); ?>
                        <tr>
                            <td>
                                <?php if ($accommodation): ?>
                                    <a href="<?= $link->url('accommodation.show', ['id' => $accommodation->id]) ?>">
                                        <?= htmlspecialchars($accommodation->nazov) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Neznáme</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d.m.Y', strtotime($reservation->datum_od)) ?></td>
                            <td><?= date('d.m.Y', strtotime($reservation->datum_do)) ?></td>
                            <td><?= $reservation->pocet_osob ?></td>
                            <td><strong><?= number_format($reservation->celkova_cena, 2, ',', ' ') ?> &euro;</strong></td>
                            <td>
                                <span class="badge bg-<?= $reservation->getStatusColor() ?>">
                                    <?= $reservation->getStatusLabel() ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= $link->url('reservation.show', ['id' => $reservation->id]) ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <?php if (in_array($reservation->stav, ['cakajuca', 'potvrdena'])): ?>
                                    <a href="<?= $link->url('reservation.cancel', ['id' => $reservation->id]) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Naozaj chcete zrušiť túto rezerváciu?')">
                                        <i class="bi bi-x-circle"></i> Zrušiť
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
