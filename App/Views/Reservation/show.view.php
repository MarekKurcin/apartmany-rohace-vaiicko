<?php
/** @var \App\Models\Reservation $reservation */
/** @var \App\Models\Accommodation $accommodation */
/** @var \App\Models\User $guest */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-<?= $reservation->getStatusColor() ?> text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Detail rezervácie #<?= $reservation->id ?></h4>
                    <span class="badge bg-light text-dark"><?= $reservation->getStatusLabel() ?></span>
                </div>
                <div class="card-body">
                    <!-- Ubytovanie -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2"><i class="bi bi-house"></i> Ubytovanie</h5>
                        <?php if ($accommodation): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong><?= htmlspecialchars($accommodation->nazov) ?></strong></p>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($accommodation->adresa) ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <a href="<?= $link->url('accommodation.show', ['id' => $accommodation->id]) ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Zobraziť ubytovanie
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Ubytovanie nie je dostupné</p>
                        <?php endif; ?>
                    </div>

                    <!-- Termín -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2"><i class="bi bi-calendar"></i> Termín pobytu</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Príchod</p>
                                <p class="h5"><?= date('d.m.Y', strtotime($reservation->datum_od)) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Odchod</p>
                                <p class="h5"><?= date('d.m.Y', strtotime($reservation->datum_do)) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Počet nocí</p>
                                <p class="h5"><?= $reservation->getNightsCount() ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detaily -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2"><i class="bi bi-info-circle"></i> Detaily</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Počet osôb</p>
                                <p class="h5"><?= $reservation->pocet_osob ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Celková cena</p>
                                <p class="h4 text-primary"><?= number_format($reservation->celkova_cena, 2, ',', ' ') ?> &euro;</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hosť (pre ubytovateľa) -->
                    <?php if ($guest): ?>
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-person"></i> Hosť</h5>
                            <p class="mb-1"><strong><?= htmlspecialchars($guest->getFullName()) ?></strong></p>
                            <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($guest->email) ?></p>
                            <?php if ($guest->telefon): ?>
                                <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($guest->telefon) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Akcie -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= $link->url('reservation.index') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Späť na zoznam
                        </a>

                        <?php if (in_array($reservation->stav, ['cakajuca', 'potvrdena'])): ?>
                            <a href="<?= $link->url('reservation.cancel', ['id' => $reservation->id]) ?>"
                               class="btn btn-danger"
                               onclick="return confirm('Naozaj chcete zrušiť túto rezerváciu?')">
                                <i class="bi bi-x-circle"></i> Zrušiť rezerváciu
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
