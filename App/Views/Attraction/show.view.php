<?php
/** @var \App\Models\Attraction $attraction */
/** @var array $nearbyAccommodations */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $link->url('home.index') ?>">Domov</a></li>
            <li class="breadcrumb-item"><a href="<?= $link->url('attraction.index') ?>">Atrakcie</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($attraction->nazov) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Hlavný obsah -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <?php if ($attraction->obrazok): ?>
                    <img src="<?= htmlspecialchars($attraction->obrazok) ?>" 
                         class="card-img-top" 
                         style="height: 400px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($attraction->nazov) ?>">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200" 
                         class="card-img-top" 
                         style="height: 400px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($attraction->nazov) ?>">
                <?php endif; ?>
                
                <div class="card-body">
                    <h1 class="card-title mb-3"><?= htmlspecialchars($attraction->nazov) ?></h1>
                    
                    <div class="mb-3">
                        <?php if ($attraction->typ): ?>
                            <span class="badge bg-info me-2">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($attraction->typ) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($attraction->poloha): ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($attraction->poloha) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($attraction->cena !== null): ?>
                        <div class="alert alert-info">
                            <strong><i class="bi bi-cash"></i> Vstupné:</strong>
                            <?php if ($attraction->cena == 0): ?>
                                <span class="text-success fw-bold">Zadarmo</span>
                            <?php else: ?>
                                <?= $attraction->getFormattedPrice() ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($attraction->popis): ?>
                        <div class="mt-4">
                            <h3>Popis</h3>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($attraction->popis)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tlačidlá pre admin -->
            <?php if (isset($user) && $user?->isLoggedIn()): ?>
                <div class="mb-3">
                    <a href="<?= $link->url('attraction.edit', ['id' => $attraction->id]) ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Upraviť
                    </a>
                    <form method="POST" action="<?= $link->url('attraction.delete', ['id' => $attraction->id]) ?>" 
                          style="display: inline;" 
                          onsubmit="return confirm('Naozaj chcete vymazať túto atrakciu?');">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Vymazať
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bočný panel -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-house"></i> Ubytovanie v okolí</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($nearbyAccommodations)): ?>
                        <div class="list-group">
                            <?php foreach ($nearbyAccommodations as $acc): ?>
                                <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($acc->nazov) ?></h6>
                                        <small><?= number_format($acc->cena_za_noc, 2) ?> €</small>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($acc->adresa) ?>
                                    </small>
                                    <?php if (isset($acc->vzdialenost_km)): ?>
                                        <br>
                                        <small class="text-info">
                                            <i class="bi bi-signpost"></i> <?= number_format($acc->vzdialenost_km, 1) ?> km
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Žiadne ubytovanie v blízkosti</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= $link->url('attraction.index') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Späť na zoznam atrakcií
        </a>
    </div>
</div>

<style>
.text-justify {
    text-align: justify;
}
</style>
