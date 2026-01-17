<?php
/** @var array $accommodations */
/** @var array $filters */
/** @var \Framework\Support\LinkGenerator $link */

$vybavenieOptions = ['WiFi', 'Parkovisko', 'Kuchyňa', 'TV', 'Krb', 'Balkón', 'Záhrada', 'Práčka'];
$selectedVybavenie = isset($filters['vybavenie']) ? array_map('trim', explode(',', $filters['vybavenie'])) : [];
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-houses"></i> Ubytovanie pod Roháčmi</h1>
        <span class="badge bg-primary fs-6" id="resultBadge">
            <i class="bi bi-building"></i> <span id="resultCount"><?= count($accommodations) ?></span>
            <?= count($accommodations) == 1 ? 'ubytovanie' : (count($accommodations) < 5 ? 'ubytovania' : 'ubytovaní') ?>
        </span>
    </div>

    <!-- AJAX Filtračný formulár -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtrovanie ubytovaní</h5>
        </div>
        <div class="card-body">
            <form id="accommodationFilterForm" action="<?= $link->url('accommodation.index') ?>" method="GET">
                <input type="hidden" name="c" value="Accommodation">

                <!-- Prvý riadok - základné filtre -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="kapacita" class="form-label"><i class="bi bi-people"></i> Počet osôb</label>
                        <select class="form-select" id="kapacita" name="kapacita">
                            <option value="">Všetky kapacity</option>
                            <option value="1" <?= ($filters['kapacita'] ?? '') == '1' ? 'selected' : '' ?>>1+ osoba</option>
                            <option value="2" <?= ($filters['kapacita'] ?? '') == '2' ? 'selected' : '' ?>>2+ osoby</option>
                            <option value="4" <?= ($filters['kapacita'] ?? '') == '4' ? 'selected' : '' ?>>4+ osoby</option>
                            <option value="6" <?= ($filters['kapacita'] ?? '') == '6' ? 'selected' : '' ?>>6+ osôb</option>
                            <option value="8" <?= ($filters['kapacita'] ?? '') == '8' ? 'selected' : '' ?>>8+ osôb</option>
                            <option value="10" <?= ($filters['kapacita'] ?? '') == '10' ? 'selected' : '' ?>>10+ osôb</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="max_cena" class="form-label"><i class="bi bi-currency-euro"></i> Max. cena/noc</label>
                        <select class="form-select" id="max_cena" name="max_cena">
                            <option value="">Bez limitu</option>
                            <option value="50" <?= ($filters['max_cena'] ?? '') == '50' ? 'selected' : '' ?>>do 50 €</option>
                            <option value="80" <?= ($filters['max_cena'] ?? '') == '80' ? 'selected' : '' ?>>do 80 €</option>
                            <option value="100" <?= ($filters['max_cena'] ?? '') == '100' ? 'selected' : '' ?>>do 100 €</option>
                            <option value="150" <?= ($filters['max_cena'] ?? '') == '150' ? 'selected' : '' ?>>do 150 €</option>
                            <option value="200" <?= ($filters['max_cena'] ?? '') == '200' ? 'selected' : '' ?>>do 200 €</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="zoradenie" class="form-label"><i class="bi bi-sort-down"></i> Zoradiť podľa</label>
                        <select class="form-select" id="zoradenie" name="zoradenie">
                            <option value="najnovsie" <?= ($filters['zoradenie'] ?? '') == 'najnovsie' ? 'selected' : '' ?>>Najnovšie</option>
                            <option value="cena_asc" <?= ($filters['zoradenie'] ?? '') == 'cena_asc' ? 'selected' : '' ?>>Cena: najnižšia</option>
                            <option value="cena_desc" <?= ($filters['zoradenie'] ?? '') == 'cena_desc' ? 'selected' : '' ?>>Cena: najvyššia</option>
                            <option value="kapacita_asc" <?= ($filters['zoradenie'] ?? '') == 'kapacita_asc' ? 'selected' : '' ?>>Kapacita: najmenšia</option>
                            <option value="kapacita_desc" <?= ($filters['zoradenie'] ?? '') == 'kapacita_desc' ? 'selected' : '' ?>>Kapacita: najväčšia</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search"></i> Filtrovať
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()" title="Zrušiť filtre">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Druhý riadok - vybavenie -->
                <div class="border-top pt-3">
                    <label class="form-label mb-2"><i class="bi bi-check2-square"></i> Vybavenie</label>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach ($vybavenieOptions as $option): ?>
                            <div class="form-check">
                                <input class="form-check-input vybavenie-checkbox" type="checkbox"
                                       id="vyb_<?= strtolower(str_replace(' ', '_', $option)) ?>"
                                       name="vybavenie_arr[]"
                                       value="<?= htmlspecialchars($option) ?>"
                                       <?= in_array($option, $selectedVybavenie) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="vyb_<?= strtolower(str_replace(' ', '_', $option)) ?>">
                                    <?= htmlspecialchars($option) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
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
