<?php
/** @var \App\Models\Accommodation $accommodation */
/** @var array|null $errors */
/** @var array|null $old */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="bi bi-pencil"></i> Upraviť ubytovanie</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Chyby vo formulári:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $link->url('accommodation.update', ['id' => $accommodation->id]) ?>">
                        <div class="mb-3">
                            <label for="nazov" class="form-label">Názov ubytovania *</label>
                            <input type="text" class="form-control <?= isset($errors['nazov']) ? 'is-invalid' : '' ?>" 
                                   id="nazov" name="nazov" required
                                   value="<?= htmlspecialchars($old['nazov'] ?? $accommodation->nazov) ?>">
                            <?php if (isset($errors['nazov'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['nazov']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="popis" class="form-label">Popis</label>
                            <textarea class="form-control" 
                                      id="popis" name="popis" rows="5"><?= htmlspecialchars($old['popis'] ?? $accommodation->popis ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="adresa" class="form-label">Adresa *</label>
                            <input type="text" class="form-control <?= isset($errors['adresa']) ? 'is-invalid' : '' ?>" 
                                   id="adresa" name="adresa" required
                                   value="<?= htmlspecialchars($old['adresa'] ?? $accommodation->adresa) ?>"
                                   placeholder="napr. Zuberec 123">
                            <?php if (isset($errors['adresa'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['adresa']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kapacita" class="form-label">Kapacita (počet osôb) *</label>
                                <input type="number" class="form-control <?= isset($errors['kapacita']) ? 'is-invalid' : '' ?>" 
                                       id="kapacita" name="kapacita" min="1" max="50" required
                                       value="<?= htmlspecialchars($old['kapacita'] ?? $accommodation->kapacita) ?>">
                                <?php if (isset($errors['kapacita'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['kapacita']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cena_za_noc" class="form-label">Cena za noc (€) *</label>
                                <input type="number" class="form-control <?= isset($errors['cena_za_noc']) ? 'is-invalid' : '' ?>" 
                                       id="cena_za_noc" name="cena_za_noc" min="0" step="0.01" required
                                       value="<?= htmlspecialchars($old['cena_za_noc'] ?? $accommodation->cena_za_noc) ?>">
                                <?php if (isset($errors['cena_za_noc'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['cena_za_noc']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vybavenie" class="form-label">Vybavenie</label>
                            <input type="text" class="form-control" 
                                   id="vybavenie" name="vybavenie" 
                                   value="<?= htmlspecialchars($old['vybavenie'] ?? $accommodation->vybavenie ?? '') ?>"
                                   placeholder="WiFi, Parkovisko, Kuchyňa, TV (oddelené čiarkou)">
                            <small class="text-muted">Jednotlivé položky oddeľte čiarkou</small>
                        </div>

                        <div class="mb-3">
                            <label for="obrazok" class="form-label">URL obrázku</label>
                            <input type="url" class="form-control" 
                                   id="obrazok" name="obrazok" 
                                   value="<?= htmlspecialchars($old['obrazok'] ?? $accommodation->obrazok ?? '') ?>"
                                   placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Zadajte URL adresu obrázku</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="aktivne" name="aktivne" value="1"
                                       <?= ($old['aktivne'] ?? $accommodation->aktivne) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="aktivne">
                                    Ubytovanie je aktívne (viditeľné pre ostatných)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $link->url('accommodation.show', ['id' => $accommodation->id]) ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Zrušiť
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Uložiť zmeny
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
