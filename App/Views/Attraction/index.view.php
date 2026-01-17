<?php
/** @var array $attractions */
/** @var array $types */
/** @var array $filters */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-geo-alt"></i> Atrakcie v okolí</h1>
        <span class="badge bg-info fs-6" id="resultBadge">
            <i class="bi bi-pin-map"></i> <span id="resultCount"><?= count($attractions) ?></span>
            <?= count($attractions) == 1 ? 'atrakcia' : (count($attractions) >= 2 && count($attractions) <= 4 ? 'atrakcie' : 'atrakcií') ?>
        </span>
    </div>

    <!-- AJAX Filtračný formulár -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtrovanie atrakcií</h5>
        </div>
        <div class="card-body">
            <form id="attractionFilterForm" action="<?= $link->url('attraction.index') ?>" method="GET">
                <input type="hidden" name="c" value="Attraction">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="typ" class="form-label"><i class="bi bi-tag"></i> Typ atrakcie</label>
                        <select class="form-select" id="typ" name="typ">
                            <option value="">Všetky typy</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= ($filters['typ'] ?? '') === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cena_filter" class="form-label"><i class="bi bi-currency-euro"></i> Cena</label>
                        <select class="form-select" id="cena_filter" name="cena_filter">
                            <option value="">Všetky</option>
                            <option value="zadarmo" <?= ($filters['cena_filter'] ?? '') === 'zadarmo' ? 'selected' : '' ?>>Zadarmo</option>
                            <option value="platene" <?= ($filters['cena_filter'] ?? '') === 'platene' ? 'selected' : '' ?>>Platené</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="zoradenie" class="form-label"><i class="bi bi-sort-down"></i> Zoradiť podľa</label>
                        <select class="form-select" id="zoradenie" name="zoradenie">
                            <option value="najnovsie" <?= ($filters['zoradenie'] ?? '') === 'najnovsie' ? 'selected' : '' ?>>Najnovšie</option>
                            <option value="nazov_asc" <?= ($filters['zoradenie'] ?? '') === 'nazov_asc' ? 'selected' : '' ?>>Názov: A-Z</option>
                            <option value="nazov_desc" <?= ($filters['zoradenie'] ?? '') === 'nazov_desc' ? 'selected' : '' ?>>Názov: Z-A</option>
                            <option value="cena_asc" <?= ($filters['zoradenie'] ?? '') === 'cena_asc' ? 'selected' : '' ?>>Cena: najnižšia</option>
                            <option value="cena_desc" <?= ($filters['zoradenie'] ?? '') === 'cena_desc' ? 'selected' : '' ?>>Cena: najvyššia</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info flex-grow-1 text-white">
                                <i class="bi bi-search"></i> Filtrovať
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearAttractionFilters()" title="Zrušiť filtre">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tlačidlo pridať atrakciu (len pre adminov) -->
    <?php if (isset($user) && $user?->isLoggedIn()): ?>
        <?php
        $currentUser = \App\Models\User::getOne($user->getId());
        if ($currentUser && $currentUser->isAdmin()):
        ?>
        <div class="mb-4">
            <a href="<?= $link->url('attraction.create') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Pridať atrakciu
            </a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Zoznam atrakcií -->
    <div class="row g-4" id="attractionGrid">
        <?php if (!empty($attractions)): ?>
            <?php foreach ($attractions as $attraction): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($attraction->obrazok ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800') ?>"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($attraction->nazov) ?>">
                            <?php if ($attraction->typ): ?>
                                <span class="position-absolute top-0 end-0 m-2 badge bg-info">
                                    <?= htmlspecialchars($attraction->typ) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($attraction->nazov) ?></h5>

                            <?php if ($attraction->poloha): ?>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($attraction->poloha) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($attraction->cena !== null): ?>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-tag"></i>
                                    <?php if ($attraction->cena == 0): ?>
                                        <strong class="text-success">Zadarmo</strong>
                                    <?php else: ?>
                                        <?= $attraction->getFormattedPrice() ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($attraction->popis): ?>
                                <p class="card-text small">
                                    <?= htmlspecialchars(substr($attraction->popis, 0, 120)) ?><?= strlen($attraction->popis) > 120 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="<?= $link->url('attraction.show', ['id' => $attraction->id]) ?>"
                               class="btn btn-outline-info w-100">
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
                    Nenašli sa žiadne atrakcie podľa zadaných kritérií.
                    <br>
                    <a href="javascript:void(0)" onclick="clearAttractionFilters()" class="alert-link">Zrušiť filtre</a>
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
