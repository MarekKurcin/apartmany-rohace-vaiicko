<?php
/** @var \App\Models\Accommodation $accommodation */
/** @var array $attractions */
/** @var array $reviews */
/** @var float|null $averageRating */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $link->url('home.index') ?>">Domov</a></li>
            <li class="breadcrumb-item"><a href="<?= $link->url('accommodation.index') ?>">Ubytovanie</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($accommodation->nazov) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Hlavný obsah -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <?php if ($accommodation->obrazok): ?>
                    <img src="<?= htmlspecialchars($accommodation->obrazok) ?>" 
                         class="card-img-top" 
                         style="height: 400px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($accommodation->nazov) ?>">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200" 
                         class="card-img-top" 
                         style="height: 400px; object-fit: cover;" 
                         alt="<?= htmlspecialchars($accommodation->nazov) ?>">
                <?php endif; ?>
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h1 class="card-title mb-0"><?= htmlspecialchars($accommodation->nazov) ?></h1>
                        <span class="badge bg-primary fs-5"><?= number_format($accommodation->cena_za_noc, 2) ?> €/noc</span>
                    </div>

                    <?php if ($averageRating): ?>
                        <div class="mb-3">
                            <span class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $averageRating): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php elseif ($i - 0.5 <= $averageRating): ?>
                                        <i class="bi bi-star-half"></i>
                                    <?php else: ?>
                                        <i class="bi bi-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </span>
                            <small class="text-muted">(<?= number_format($averageRating, 1) ?> z <?= count($reviews) ?> hodnotení)</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <strong>Adresa:</strong> <?= htmlspecialchars($accommodation->adresa) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="bi bi-people text-primary"></i>
                                <strong>Kapacita:</strong> <?= $accommodation->kapacita ?> osôb
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($accommodation->popis): ?>
                        <div class="mt-4">
                            <h3>Popis</h3>
                            <p class="text-justify"><?= nl2br(htmlspecialchars($accommodation->popis)) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($accommodation->vybavenie): ?>
                        <div class="mt-4">
                            <h3>Vybavenie</h3>
                            <div class="row">
                                <?php foreach ($accommodation->getVybavenieArray() as $item): ?>
                                    <div class="col-md-6 mb-2">
                                        <i class="bi bi-check-circle text-success"></i> <?= htmlspecialchars($item) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hodnotenia -->
            <?php if (!empty($reviews)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h3 class="mb-0"><i class="bi bi-star"></i> Hodnotenia (<?= count($reviews) ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($reviews as $review): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($review->getUser()?->meno ?? 'Používateľ') ?></strong>
                                        <div class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $review->hodnotenie ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= date('d.m.Y', strtotime($review->created_at)) ?></small>
                                </div>
                                <?php if ($review->komentar): ?>
                                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($review->komentar)) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tlačidlá pre vlastníka/admina -->
            <?php if (isset($user) && $user?->isLoggedIn()): ?>
                <div class="mb-3">
                    <a href="<?= $link->url('accommodation.edit', ['id' => $accommodation->id]) ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Upraviť
                    </a>
                    <form method="POST" action="<?= $link->url('accommodation.delete', ['id' => $accommodation->id]) ?>" 
                          style="display: inline;" 
                          onsubmit="return confirm('Naozaj chcete vymazať toto ubytovanie?');">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Vymazať
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bočný panel -->
        <div class="col-lg-4">
            <!-- Atrakcie v okolí -->
            <?php if (!empty($attractions)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-map"></i> Atrakcie v okolí</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($attractions as $attr): ?>
                                <a href="<?= $link->url('attraction.show', ['id' => $attr->id]) ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($attr->nazov) ?></h6>
                                        <?php if ($attr->typ): ?>
                                            <small class="badge bg-info"><?= htmlspecialchars($attr->typ) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($attr->vzdialenost_km)): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-signpost"></i> <?= number_format($attr->vzdialenost_km, 1) ?> km
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Rezervačný formulár (placeholder) -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Rezervácia</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="datum_od" class="form-label">Dátum príchodu</label>
                        <input type="date" class="form-control" id="datum_od" name="datum_od">
                    </div>
                    <div class="mb-3">
                        <label for="datum_do" class="form-label">Dátum odchodu</label>
                        <input type="date" class="form-control" id="datum_do" name="datum_do">
                    </div>
                    <div class="mb-3">
                        <label for="pocet_osob" class="form-label">Počet osôb</label>
                        <input type="number" class="form-control" id="pocet_osob" name="pocet_osob" 
                               min="1" max="<?= $accommodation->kapacita ?>" value="2">
                    </div>
                    <button type="button" class="btn btn-success w-100" disabled>
                        <i class="bi bi-calendar-plus"></i> Rezervovať (už čoskoro)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= $link->url('accommodation.index') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Späť na zoznam ubytovaní
        </a>
    </div>
</div>

<style>
.text-justify {
    text-align: justify;
}
</style>
