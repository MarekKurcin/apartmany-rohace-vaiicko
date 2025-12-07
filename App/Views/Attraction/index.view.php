<?php
/** @var array $attractions */
/** @var array $types */
/** @var string|null $selectedType */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <h1 class="mb-4">Atrakcie v okolí</h1>

    <!-- Filter podľa typu -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= $link->url('attraction.index') ?>" 
                   class="btn <?= empty($selectedType) ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Všetky
                </a>
                <?php if (!empty($types)): ?>
                    <?php foreach ($types as $type): ?>
                        <a href="<?= $link->url('attraction.index', ['typ' => $type]) ?>" 
                           class="btn <?= $selectedType === $type ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <?= htmlspecialchars($type) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tlačidlo pridať atrakciu (len pre adminov) -->
    <?php if (isset($user) && $user?->isLoggedIn()): ?>
        <div class="mb-4">
            <a href="<?= $link->url('attraction.create') ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Pridať atrakciu
            </a>
        </div>
    <?php endif; ?>

    <!-- Zoznam atrakcií -->
    <div class="row g-4">
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
                               class="btn btn-primary w-100">
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
                    <?php if (!empty($selectedType)): ?>
                        Nenašli sa žiadne atrakcie typu "<?= htmlspecialchars($selectedType) ?>".
                        <br>
                        <a href="<?= $link->url('attraction.index') ?>" class="alert-link">Zobraziť všetky atrakcie</a>
                    <?php else: ?>
                        Momentálne nemáme žiadne atrakcie v databáze.
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

.btn-group .btn {
    margin: 0;
}
</style>
