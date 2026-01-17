<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var array $errors */
/** @var array $old */

$errors = $errors ?? [];
$old = $old ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $link->url('home.index') ?>">Domov</a></li>
            <li class="breadcrumb-item"><a href="<?= $link->url('accommodation.index') ?>">Ubytovanie</a></li>
            <li class="breadcrumb-item active">Pridať ubytovanie</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-plus-lg"></i> Pridať nové ubytovanie</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $link->url('accommodation.store') ?>"
                          id="accommodationForm" enctype="multipart/form-data" novalidate>
                        
                        <div class="mb-3">
                            <label for="nazov" class="form-label">Názov ubytovania *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nazov" 
                                   name="nazov" 
                                   value="<?= htmlspecialchars($old['nazov'] ?? '') ?>" 
                                   required 
                                   minlength="3">
                            <div class="invalid-feedback">Názov musí mať minimálne 3 znaky</div>
                        </div>

                        <div class="mb-3">
                            <label for="popis" class="form-label">Popis</label>
                            <textarea class="form-control" 
                                      id="popis" 
                                      name="popis" 
                                      rows="4"><?= htmlspecialchars($old['popis'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="adresa" class="form-label">Adresa *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="adresa" 
                                   name="adresa" 
                                   value="<?= htmlspecialchars($old['adresa'] ?? '') ?>" 
                                   required 
                                   minlength="5">
                            <div class="invalid-feedback">Adresa musí mať minimálne 5 znakov</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kapacita" class="form-label">Kapacita (počet osôb) *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="kapacita" 
                                       name="kapacita" 
                                       value="<?= htmlspecialchars($old['kapacita'] ?? '') ?>" 
                                       required 
                                       min="1" 
                                       max="50">
                                <div class="invalid-feedback">Kapacita musí byť medzi 1 a 50</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cena_za_noc" class="form-label">Cena za noc (€) *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="cena_za_noc" 
                                       name="cena_za_noc" 
                                       value="<?= htmlspecialchars($old['cena_za_noc'] ?? '') ?>" 
                                       required 
                                       min="1" 
                                       step="0.01">
                                <div class="invalid-feedback">Zadajte platnú cenu</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vybavenie" class="form-label">Vybavenie</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="vybavenie" 
                                   name="vybavenie" 
                                   value="<?= htmlspecialchars($old['vybavenie'] ?? '') ?>"
                                   placeholder="WiFi, Parkovisko, TV, Kuchyňa...">
                            <small class="text-muted">Oddeľte jednotlivé položky čiarkou</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fotografia ubytovania</label>
                            <div class="mb-2">
                                <label for="obrazok" class="form-label small text-muted">Nahrat subor:</label>
                                <input type="file"
                                       class="form-control"
                                       id="obrazok"
                                       name="obrazok"
                                       accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">Povolene formaty: JPG, PNG, WebP. Max velkost: 5MB</small>
                            </div>
                            <div class="mb-2">
                                <label for="obrazok_url" class="form-label small text-muted">Alebo zadat URL:</label>
                                <input type="url"
                                       class="form-control"
                                       id="obrazok_url"
                                       name="obrazok_url"
                                       value="<?= htmlspecialchars($old['obrazok_url'] ?? '') ?>"
                                       placeholder="https://example.com/obrazok.jpg">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="aktivne" 
                                   name="aktivne" 
                                   checked>
                            <label class="form-check-label" for="aktivne">
                                Aktívne (viditeľné pre návštevníkov)
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Uložiť
                            </button>
                            <a href="<?= $link->url('accommodation.index') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Zrušiť
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
