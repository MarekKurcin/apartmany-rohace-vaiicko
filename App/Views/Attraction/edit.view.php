<?php
/** @var \App\Models\Attraction $attraction */
/** @var array|null $errors */
/** @var array|null $old */
/** @var \Framework\Support\LinkGenerator $link */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="bi bi-pencil"></i> Upraviť atrakciu</h3>
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

                    <form method="POST" action="<?= $link->url('attraction.update', ['id' => $attraction->id]) ?>">
                        <div class="mb-3">
                            <label for="nazov" class="form-label">Názov atrakcie *</label>
                            <input type="text" class="form-control <?= isset($errors['nazov']) ? 'is-invalid' : '' ?>" 
                                   id="nazov" name="nazov" required
                                   value="<?= htmlspecialchars($old['nazov'] ?? $attraction->nazov) ?>">
                            <?php if (isset($errors['nazov'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['nazov']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="popis" class="form-label">Popis *</label>
                            <textarea class="form-control <?= isset($errors['popis']) ? 'is-invalid' : '' ?>" 
                                      id="popis" name="popis" rows="5" required><?= htmlspecialchars($old['popis'] ?? $attraction->popis) ?></textarea>
                            <?php if (isset($errors['popis'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['popis']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="typ" class="form-label">Typ</label>
                                <select class="form-select" id="typ" name="typ">
                                    <option value="">-- Vyberte typ --</option>
                                    <option value="Turistika" <?= ($old['typ'] ?? $attraction->typ) === 'Turistika' ? 'selected' : '' ?>>Turistika</option>
                                    <option value="Lyžovanie" <?= ($old['typ'] ?? $attraction->typ) === 'Lyžovanie' ? 'selected' : '' ?>>Lyžovanie</option>
                                    <option value="Kultúra" <?= ($old['typ'] ?? $attraction->typ) === 'Kultúra' ? 'selected' : '' ?>>Kultúra</option>
                                    <option value="Športy" <?= ($old['typ'] ?? $attraction->typ) === 'Športy' ? 'selected' : '' ?>>Športy</option>
                                    <option value="Príroda" <?= ($old['typ'] ?? $attraction->typ) === 'Príroda' ? 'selected' : '' ?>>Príroda</option>
                                    <option value="Iné" <?= ($old['typ'] ?? $attraction->typ) === 'Iné' ? 'selected' : '' ?>>Iné</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cena" class="form-label">Vstupné (€)</label>
                                <input type="number" class="form-control <?= isset($errors['cena']) ? 'is-invalid' : '' ?>" 
                                       id="cena" name="cena" min="0" step="1"
                                       value="<?= htmlspecialchars($old['cena'] ?? $attraction->cena ?? 0) ?>">
                                <small class="text-muted">Zadajte 0 pre bezplatný vstup</small>
                                <?php if (isset($errors['cena'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['cena']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="poloha" class="form-label">Poloha</label>
                            <input type="text" class="form-control" 
                                   id="poloha" name="poloha" 
                                   value="<?= htmlspecialchars($old['poloha'] ?? $attraction->poloha ?? '') ?>"
                                   placeholder="napr. Zuberec">
                        </div>

                        <div class="mb-3">
                            <label for="obrazok" class="form-label">URL obrázku</label>
                            <input type="url" class="form-control" 
                                   id="obrazok" name="obrazok" 
                                   value="<?= htmlspecialchars($old['obrazok'] ?? $attraction->obrazok ?? '') ?>"
                                   placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Zadajte URL adresu obrázku</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $link->url('attraction.show', ['id' => $attraction->id]) ?>" 
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
