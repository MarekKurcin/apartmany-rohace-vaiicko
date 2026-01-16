<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array $featuredAttractions */
/** @var array $featuredAccommodations */
?>

<!-- Hero sekcia -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title">Objavte krásy Roháčov</h1>
        <p class="hero-subtitle">Nájdite si dokonalé ubytovanie v srdci Západných Tatier</p>
        
        <!-- Vyhľadávací formulár -->
        <div class="search-box">
            <form action="<?= $link->url('accommodation.index') ?>" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kapacita</label>
                    <select class="form-select" name="kapacita">
                        <option value="">Všetky</option>
                        <option value="2">2 osoby</option>
                        <option value="4">4 osoby</option>
                        <option value="6">6+ osôb</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max. cena/noc</label>
                    <input type="number" class="form-control" name="max_cena" placeholder="€">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vybavenie</label>
                    <input type="text" class="form-control" name="vybavenie" placeholder="WiFi, Parkovisko...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Hľadať
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Sekcia - Prečo si vybrať nás -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Prečo si vybrať ubytovanie u nás?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-currency-euro"></i>
                    </div>
                    <h4>Férové ceny</h4>
                    <p>Bez vysokých provízií rezervačných portálov. Platíte priamo ubytovateľom.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h4>Lokálni poskytovatelia</h4>
                    <p>Podporujete miestnych obyvateľov a získavate autentický zážitok.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-tree"></i>
                    </div>
                    <h4>Krásna príroda</h4>
                    <p>Západné Tatry ponúkajú nezabudnuteľné výhľady a turistické trasy.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sekcia - Populárne atrakcie -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Objavte okolie</h2>
        <div class="row g-4">
            <?php if (!empty($featuredAttractions)): ?>
                <?php foreach ($featuredAttractions as $attr): ?>
                    <div class="col-md-4">
                        <div class="attraction-card">
                            <div class="attraction-image" style="background-image: url('<?= htmlspecialchars($attr->obrazok ?? 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800') ?>');">
                                <span class="attraction-badge"><?= htmlspecialchars($attr->typ ?? 'Iné') ?></span>
                            </div>
                            <div class="attraction-content p-3">
                                <h5><?= htmlspecialchars($attr->nazov) ?></h5>
                                <p><?= htmlspecialchars(substr($attr->popis ?? '', 0, 100)) ?><?= strlen($attr->popis ?? '') > 100 ? '...' : '' ?></p>
                                <a href="<?= $link->url('attraction.show', ['id' => $attr->id]) ?>" class="btn btn-sm btn-outline-primary">
                                    Viac info <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Momentálne nemáme žiadne atrakcie.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= $link->url('attraction.index') ?>" class="btn btn-primary">Zobraziť všetky atrakcie</a>
        </div>
    </div>
</section>

<!-- Sekcia - Odporúčané ubytovanie -->
<?php if (!empty($featuredAccommodations)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Odporúčané ubytovanie</h2>
        <div class="row g-4">
            <?php foreach ($featuredAccommodations as $acc): ?>
                <div class="col-md-4">
                    <div class="accommodation-card">
                        <div class="accommodation-image" style="background-image: url('<?= htmlspecialchars($acc->obrazok ?? 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800') ?>');">
                            <span class="price-badge"><?= number_format($acc->cena_za_noc, 2) ?> €/noc</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($acc->nazov) ?></h5>
                            <p class="text-muted mb-2 odsek-test">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($acc->adresa) ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-people"></i> Kapacita: <?= $acc->kapacita ?> osôb
                            </p>
                            <div class="accommodation-features">
                                <?php
                                $features = $acc->getVybavenieArray();
                                foreach (array_slice($features, 0, 3) as $feature):
                                ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($feature) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($features) > 3): ?>
                                    <span class="badge bg-light text-dark">+<?= count($features) - 3 ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>" class="btn btn-primary w-100">
                                    Zobraziť detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= $link->url('accommodation.index') ?>" class="btn btn-primary">Zobraziť všetky ubytovania</a>
        </div>
    </div>
</section>
<?php endif; ?>
