<?php
/** @var \App\Models\Accommodation $accommodation */
/** @var array $errors */
/** @var array $old */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-calendar-plus"></i> Rezervácia ubytovania</h4>
                </div>
                <div class="card-body">
                    <!-- Info o ubytovaní -->
                    <div class="card bg-light border mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1"><?= htmlspecialchars($accommodation->nazov) ?></h5>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($accommodation->adresa) ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="bi bi-people"></i> Kapacita: <?= $accommodation->kapacita ?> osôb
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <span class="h4 text-primary"><?= number_format($accommodation->cena_za_noc, 2, ',', ' ') ?> &euro;</span>
                                    <small class="text-muted d-block">za noc</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $link->url('reservation.store') ?>" id="reservationForm">
                        <input type="hidden" name="accommodation_id" value="<?= $accommodation->id ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="datum_od" class="form-label">Dátum príchodu *</label>
                                <input type="date" class="form-control <?= isset($errors['datum_od']) ? 'is-invalid' : '' ?>"
                                       id="datum_od" name="datum_od"
                                       value="<?= htmlspecialchars($old['datum_od'] ?? '') ?>"
                                       min="<?= date('Y-m-d') ?>" required>
                                <?php if (isset($errors['datum_od'])): ?>
                                    <div class="invalid-feedback"><?= $errors['datum_od'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="datum_do" class="form-label">Dátum odchodu *</label>
                                <input type="date" class="form-control <?= isset($errors['datum_do']) ? 'is-invalid' : '' ?>"
                                       id="datum_do" name="datum_do"
                                       value="<?= htmlspecialchars($old['datum_do'] ?? '') ?>"
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                <?php if (isset($errors['datum_do'])): ?>
                                    <div class="invalid-feedback"><?= $errors['datum_do'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="pocet_osob" class="form-label">Počet osôb *</label>
                            <input type="number" class="form-control <?= isset($errors['pocet_osob']) ? 'is-invalid' : '' ?>"
                                   id="pocet_osob" name="pocet_osob"
                                   value="<?= htmlspecialchars($old['pocet_osob'] ?? '1') ?>"
                                   min="1" max="<?= $accommodation->kapacita ?>" required>
                            <div class="form-text">Maximálna kapacita: <?= $accommodation->kapacita ?> osôb</div>
                            <?php if (isset($errors['pocet_osob'])): ?>
                                <div class="invalid-feedback"><?= $errors['pocet_osob'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Kalkulácia ceny -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Kalkulácia ceny</h6>
                                <div class="row">
                                    <div class="col-6">Počet nocí:</div>
                                    <div class="col-6 text-end"><span id="pocetNoci">0</span></div>
                                </div>
                                <div class="row">
                                    <div class="col-6">Cena za noc:</div>
                                    <div class="col-6 text-end"><?= number_format($accommodation->cena_za_noc, 2, ',', ' ') ?> &euro;</div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6"><strong>Celková cena:</strong></div>
                                    <div class="col-6 text-end">
                                        <strong class="text-primary h5" id="celkovaCena">0,00 &euro;</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Odoslať rezerváciu
                            </button>
                            <a href="<?= $link->url('accommodation.show', ['id' => $accommodation->id]) ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Späť na ubytovanie
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const cenaZaNoc = <?= $accommodation->cena_za_noc ?>;

function calculatePrice() {
    const datumOd = document.getElementById('datum_od').value;
    const datumDo = document.getElementById('datum_do').value;

    if (datumOd && datumDo) {
        const od = new Date(datumOd);
        const do_ = new Date(datumDo);
        const diffTime = do_ - od;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0) {
            document.getElementById('pocetNoci').textContent = diffDays;
            const celkova = diffDays * cenaZaNoc;
            document.getElementById('celkovaCena').textContent = celkova.toLocaleString('sk-SK', {minimumFractionDigits: 2}) + ' €';
        } else {
            document.getElementById('pocetNoci').textContent = '0';
            document.getElementById('celkovaCena').textContent = '0,00 €';
        }
    }
}

document.getElementById('datum_od').addEventListener('change', function() {
    // Nastaviť minimum pre dátum odchodu
    const minDatumDo = new Date(this.value);
    minDatumDo.setDate(minDatumDo.getDate() + 1);
    document.getElementById('datum_do').min = minDatumDo.toISOString().split('T')[0];
    calculatePrice();
});

document.getElementById('datum_do').addEventListener('change', calculatePrice);

// Počiatočný výpočet
calculatePrice();
</script>
