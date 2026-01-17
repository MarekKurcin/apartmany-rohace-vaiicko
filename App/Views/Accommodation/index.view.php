<?php
/** @var array $accommodations */
/** @var array $filters */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Ubytovanie pod Roháčmi</h1>
        <span class="badge bg-primary fs-6">
            <span id="resultCount"><?= count($accommodations) ?></span> ubytovaní
        </span>
    </div>

    <!-- AJAX Filtračný formulár -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="accommodationFilterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="kapacita" class="form-label">Minimálna kapacita</label>
                        <input type="number" class="form-control" id="kapacita" name="kapacita"
                               min="1" value="<?= htmlspecialchars($filters['kapacita'] ?? '') ?>"
                               placeholder="Počet osôb">
                    </div>
                    <div class="col-md-3">
                        <label for="max_cena" class="form-label">Maximálna cena/noc</label>
                        <input type="number" class="form-control" id="max_cena" name="max_cena"
                               min="0" step="0.01" value="<?= htmlspecialchars($filters['max_cena'] ?? '') ?>"
                               placeholder="€">
                    </div>
                    <div class="col-md-4">
                        <label for="vybavenie" class="form-label">Vybavenie</label>
                        <input type="text" class="form-control" id="vybavenie" name="vybavenie"
                               value="<?= htmlspecialchars($filters['vybavenie'] ?? '') ?>"
                               placeholder="WiFi, Parkovisko...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search"></i> Hľadať
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()" title="Zrušiť filtre">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </form>
            <!-- Loading indicator -->
            <div id="filterLoading" class="text-center mt-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Načítavam...</span>
                </div>
                <p class="text-muted mt-2">Vyhľadávam ubytovania...</p>
            </div>
        </div>
    </div>

    <!-- Tlačidlo pridať ubytovanie (pre prihlásených ubytovateľov) -->
    <?php if (isset($auth) && $auth->isLogged()): ?>
        <div class="mb-4">
            <a href="<?= $link->url('accommodation.create') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Pridať ubytovanie
            </a>
        </div>
    <?php endif; ?>

    <!-- Zoznam ubytovaní (AJAX container) -->
    <div class="row g-4" id="accommodationGrid">
        <?php if (!empty($accommodations)): ?>
            <?php foreach ($accommodations as $acc): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($acc->obrazok ?? 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800') ?>" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;" 
                                 alt="<?= htmlspecialchars($acc->nazov) ?>">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-primary fs-6">
                                <?= number_format($acc->cena_za_noc, 2) ?> €/noc
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($acc->nazov) ?></h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($acc->adresa) ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-people"></i> Kapacita: <?= $acc->kapacita ?> osôb
                            </p>
                            
                            <?php if ($acc->popis): ?>
                                <p class="card-text small">
                                    <?= htmlspecialchars(substr($acc->popis, 0, 100)) ?><?= strlen($acc->popis) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($acc->vybavenie): ?>
                                <div class="mb-3">
                                    <?php 
                                    $features = $acc->getVybavenieArray();
                                    foreach (array_slice($features, 0, 3) as $feature):
                                    ?>
                                        <span class="badge bg-secondary me-1"><?= htmlspecialchars($feature) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($features) > 3): ?>
                                        <span class="badge bg-light text-dark">+<?= count($features) - 3 ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>"
                               class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Zobraziť detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <i class="bi bi-info-circle"></i> 
                    Nenašli sa žiadne ubytovania podľa zadaných kritérií.
                    <?php if (!empty(array_filter($filters))): ?>
                        <br>
                        <a href="<?= $link->url('accommodation.index') ?>" class="alert-link">Zrušiť filtre</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
}
</style>
