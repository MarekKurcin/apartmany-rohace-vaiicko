<?php
/** @var array $accommodations */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-house"></i> Moje ubytovania</h1>
        <div>
            <a href="<?= $link->url('reservation.manage') ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-calendar-week"></i> Správa rezervácií
            </a>
            <a href="<?= $link->url('accommodation.create') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Pridať ubytovanie
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 'created':
                    echo 'Ubytovanie bolo vytvorené.';
                    break;
                case 'updated':
                    echo 'Ubytovanie bolo aktualizované.';
                    break;
                case 'deleted':
                    echo 'Ubytovanie bolo vymazané.';
                    break;
                default:
                    echo 'Operácia prebehla úspešne.';
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <?php if ($acc->obrazok): ?>
                                    <img src="<?= htmlspecialchars($acc->obrazok) ?>"
                                         class="img-fluid rounded-start h-100"
                                         style="object-fit: cover; min-height: 200px;"
                                         alt="<?= htmlspecialchars($acc->nazov) ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400"
                                         class="img-fluid rounded-start h-100"
                                         style="object-fit: cover; min-height: 200px;"
                                         alt="<?= htmlspecialchars($acc->nazov) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($acc->nazov) ?>
                                            </a>
                                        </h5>
                                        <?php if ($acc->aktivne): ?>
                                            <span class="badge bg-success">Aktívne</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Neaktívne</span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($acc->adresa) ?>
                                    </p>

                                    <p class="mb-3">
                                        <span class="badge bg-primary"><?= number_format($acc->cena_za_noc, 0) ?> &euro;/noc</span>
                                        <span class="badge bg-secondary"><?= $acc->kapacita ?> osôb</span>
                                    </p>

                                    <!-- Štatistiky -->
                                    <div class="row text-center mb-3 g-2">
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold text-warning"><?= $stats['cakajuce'] ?></div>
                                                <small class="text-muted">Čaká</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold text-success"><?= $stats['potvrdene'] ?></div>
                                                <small class="text-muted">Aktívne</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold text-info"><?= $stats['dokoncene'] ?></div>
                                                <small class="text-muted">Hotové</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded p-2">
                                                <div class="fw-bold" style="color: var(--gold-color)"><?= number_format($stats['celkovy_prijem'], 0) ?>&euro;</div>
                                                <small class="text-muted">Príjem</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Akcie -->
                                    <div class="d-flex gap-2">
                                        <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="<?= $link->url('accommodation.edit', ['id' => $acc->id]) ?>"
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Upraviť
                                        </a>
                                        <form method="POST" action="<?= $link->url('accommodation.delete', ['id' => $acc->id]) ?>"
                                              style="display: inline;"
                                              onsubmit="return confirm('Naozaj chcete vymazať toto ubytovanie?');">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Vymazať
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Súhrnné štatistiky -->
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
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4 class="mb-0"><?= count($accommodations) ?></h4>
                        <small class="text-muted">Počet ubytovaní</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="mb-0 text-warning"><?= $celkomCakajuce ?> / <span class="text-success"><?= $celkomPotvrdene ?></span></h4>
                        <small class="text-muted">Čakajúce / Potvrdené rezervácie</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="mb-0" style="color: var(--gold-color)"><?= number_format($celkomPrijem, 0, ',', ' ') ?> &euro;</h4>
                        <small class="text-muted">Celkový príjem</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
