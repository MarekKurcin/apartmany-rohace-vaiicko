<?php
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

                    <form method="POST" action="<?= $link->url('accommodation.update', ['id' => $accommodation->id]) ?>"
                          enctype="multipart/form-data">
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
                            <label class="form-label">Fotografia ubytovania</label>
                            <?php if ($accommodation->obrazok): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($accommodation->obrazok) ?>"
                                         alt="Aktualna fotografia"
                                         class="img-thumbnail"
                                         style="max-height: 150px;">
                                    <p class="text-muted small mt-1">Aktualna fotografia (nahrajte novu alebo zadajte URL pre zmenu)</p>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <label for="obrazok" class="form-label small text-muted">Nahrat subor:</label>
                                <input type="file" class="form-control"
                                       id="obrazok" name="obrazok"
                                       accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">Povolene formaty: JPG, PNG, WebP. Max velkost: 5MB</small>
                            </div>
                            <div class="mb-2">
                                <label for="obrazok_url" class="form-label small text-muted">Alebo zadat URL:</label>
                                <input type="url" class="form-control"
                                       id="obrazok_url" name="obrazok_url"
                                       value="<?= htmlspecialchars($old['obrazok_url'] ?? '') ?>"
                                       placeholder="https://example.com/obrazok.jpg">
                            </div>
                        </div>

                        <!-- Galéria obrázkov -->
                        <div class="mb-3">
                            <label class="form-label">Galéria obrázkov</label>
                            <div id="galleryImages" class="row g-2 mb-3">
                                <?php foreach ($accommodation->getImages() as $img): ?>
                                    <div class="col-4 col-md-3 gallery-item" data-id="<?= $img->id ?>">
                                        <div class="position-relative">
                                            <img src="<?= htmlspecialchars($img->image_path) ?>"
                                                 class="img-thumbnail w-100"
                                                 style="height: 80px; object-fit: cover;">
                                            <?php if ($img->is_primary): ?>
                                                <span class="position-absolute top-0 start-0 badge bg-success m-1" style="font-size: 0.6rem;">Hlavný</span>
                                            <?php endif; ?>
                                            <div class="position-absolute bottom-0 end-0 m-1">
                                                <?php if (!$img->is_primary): ?>
                                                    <button type="button" class="btn btn-xs btn-success p-1" onclick="setPrimaryImage(<?= $img->id ?>)" title="Nastaviť ako hlavný">
                                                        <i class="bi bi-star" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-xs btn-danger p-1" onclick="deleteGalleryImage(<?= $img->id ?>)" title="Vymazať">
                                                    <i class="bi bi-trash" style="font-size: 0.7rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" multiple accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Môžete nahrať viac obrázkov naraz (max 10 celkovo). JPG, PNG, WebP do 5MB.</small>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="uploadGalleryImages()">
                                <i class="bi bi-upload"></i> Nahrať do galérie
                            </button>
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

<script>
const accommodationId = <?= $accommodation->id ?>;

function uploadGalleryImages() {
    const input = document.getElementById('gallery_images');
    if (!input.files.length) {
        alert('Vyberte obrázky na nahratie');
        return;
    }

    const formData = new FormData();
    formData.append('accommodation_id', accommodationId);
    for (let file of input.files) {
        formData.append('gallery_images[]', file);
    }

    fetch('?c=Accommodation&a=uploadGalleryImages', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Nahraných ' + data.uploaded + ' obrázkov');
            location.reload();
        } else {
            alert('Chyba: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Nastala chyba pri nahrávaní');
    });
}

function deleteGalleryImage(imageId) {
    if (!confirm('Naozaj chcete vymazať tento obrázok?')) return;

    fetch('?c=Accommodation&a=deleteGalleryImage', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'image_id=' + imageId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.gallery-item[data-id="' + imageId + '"]').remove();
        } else {
            alert('Chyba: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Nastala chyba pri mazaní');
    });
}

function setPrimaryImage(imageId) {
    fetch('?c=Accommodation&a=setPrimaryImage', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'image_id=' + imageId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Chyba: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Nastala chyba');
    });
}
</script>
