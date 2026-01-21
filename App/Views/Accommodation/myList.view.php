<?php
$celkomPrijem = 0;
$celkomCakajuce = 0;
$celkomPotvrdene = 0;
foreach ($accommodations as $item) {
    $celkomPrijem += $item['stats']['celkovy_prijem'];
    $celkomCakajuce += $item['stats']['cakajuce'];
    $celkomPotvrdene += $item['stats']['potvrdene'];
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-house"></i> Moje ubytovania</h1>
        <a href="<?= $link->url('accommodation.create') ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Pridať ubytovanie
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 'created': echo 'Ubytovanie bolo vytvorené.'; break;
                case 'updated': echo 'Ubytovanie bolo aktualizované.'; break;
                case 'deleted': echo 'Ubytovanie bolo vymazané.'; break;
                default: echo 'Operácia prebehla úspešne.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($accommodations)): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="display-6 fw-bold" style="color: var(--primary-color)"><?= count($accommodations) ?></div>
                <small class="text-muted">Ubytovaní</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="display-6 fw-bold">
                    <span class="text-warning"><?= $celkomCakajuce ?></span> /
                    <span class="text-success"><?= $celkomPotvrdene ?></span>
                </div>
                <small class="text-muted">Čakajúce / Aktívne rezervácie</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="display-6 fw-bold" style="color: var(--gold-color)"><?= number_format($celkomPrijem, 0, ',', ' ') ?> &euro;</div>
                <small class="text-muted">Celkový príjem</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($accommodations)): ?>
        <div class="text-center py-5">
            <i class="bi bi-house display-1 text-muted"></i>
            <h3 class="mt-4 text-muted">Zatiaľ nemáte žiadne ubytovania</h3>
            <p class="text-muted">Začnite pridaním svojho prvého ubytovania.</p>
            <a href="<?= $link->url('accommodation.create') ?>" class="btn btn-success btn-lg mt-3">
                <i class="bi bi-plus-circle"></i> Pridať ubytovanie
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($accommodations as $item):
                $acc = $item['accommodation'];
                $stats = $item['stats'];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <?php if ($acc->obrazok): ?>
                                <img src="<?= htmlspecialchars($acc->obrazok) ?>"
                                     class="card-img-top"
                                     style="height: 180px; object-fit: cover;"
                                     alt="<?= htmlspecialchars($acc->nazov) ?>">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400"
                                     class="card-img-top"
                                     style="height: 180px; object-fit: cover;"
                                     alt="<?= htmlspecialchars($acc->nazov) ?>">
                            <?php endif; ?>

                            <div class="position-absolute top-0 end-0 m-2">
                                <?php if ($acc->aktivne): ?>
                                    <span class="badge bg-success">Aktívne</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Neaktívne</span>
                                <?php endif; ?>
                            </div>

                            <div class="position-absolute bottom-0 start-0 m-2">
                                <span class="badge" style="background-color: var(--gold-color)">
                                    <?= number_format($acc->cena_za_noc, 0) ?> &euro;/noc
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($acc->nazov) ?>
                                </a>
                            </h5>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($acc->adresa) ?>
                                <span class="ms-2"><i class="bi bi-people"></i> <?= $acc->kapacita ?></span>
                            </p>

                            <!-- Mini štatistiky -->
                            <div class="d-flex justify-content-between text-center border-top pt-3">
                                <div>
                                    <div class="fw-bold text-warning"><?= $stats['cakajuce'] ?></div>
                                    <small class="text-muted">Čaká</small>
                                </div>
                                <div>
                                    <div class="fw-bold text-success"><?= $stats['potvrdene'] ?></div>
                                    <small class="text-muted">Aktívne</small>
                                </div>
                                <div>
                                    <div class="fw-bold text-info"><?= $stats['celkom'] ?></div>
                                    <small class="text-muted">Celkom</small>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: var(--gold-color)"><?= number_format($stats['celkovy_prijem'], 0) ?>&euro;</div>
                                    <small class="text-muted">Príjem</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer border-top-0">
                            <div class="d-flex gap-2">
                                <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>"
                                   class="btn btn-sm btn-outline-secondary flex-fill">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="<?= $link->url('accommodation.edit', ['id' => $acc->id]) ?>"
                                   class="btn btn-sm btn-warning flex-fill">
                                    <i class="bi bi-pencil"></i> Upraviť
                                </a>
                                <form method="POST" action="<?= $link->url('accommodation.delete', ['id' => $acc->id]) ?>"
                                      onsubmit="return confirm('Naozaj chcete vymazať toto ubytovanie?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
