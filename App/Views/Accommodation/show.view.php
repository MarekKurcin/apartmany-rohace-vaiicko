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

                    <div class="mb-3" id="averageRating">
                        <?php if ($averageRating): ?>
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
                        <?php else: ?>
                            <span class="text-muted">Zatiaľ bez hodnotení</span>
                        <?php endif; ?>
                    </div>
                    
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="mb-0" id="reviewsHeader"><i class="bi bi-star"></i> Hodnotenia (<?= count($reviews) ?>)</h3>
                </div>
                <div class="card-body">
                    <!-- Alert container pre AJAX správy -->
                    <div id="reviewAlerts"></div>

                    <!-- Formulár pre pridanie recenzie (AJAX) -->
                    <?php
                    $canReview = $user->isLoggedIn();
                    $hasReviewed = false;
                    if ($canReview) {
                        $hasReviewed = \App\Models\Review::hasUserReviewed($user->getId(), $accommodation->id);
                    }
                    ?>

                    <?php if ($canReview && !$hasReviewed): ?>
                        <div class="mb-4 p-3 bg-light rounded">
                            <h5><i class="bi bi-pencil-square"></i> Pridať hodnotenie</h5>
                            <form id="reviewForm">
                                <input type="hidden" name="accommodation_id" value="<?= $accommodation->id ?>">
                                <input type="hidden" name="hodnotenie" id="reviewRating" value="">

                                <div class="mb-3">
                                    <label class="form-label">Vaše hodnotenie</label>
                                    <div class="rating-input fs-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star rating-star text-warning" data-value="<?= $i ?>" style="cursor: pointer;"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reviewComment" class="form-label">Komentár (voliteľný)</label>
                                    <textarea class="form-control" id="reviewComment" name="komentar" rows="3"
                                              maxlength="1000" placeholder="Podeľte sa o svoj zážitok..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Odoslať hodnotenie
                                </button>
                            </form>
                        </div>

<script>
// Inline debug pre recenzie
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inline review script loaded');

    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('reviewRating');
    const reviewForm = document.getElementById('reviewForm');

    console.log('Stars found:', stars.length);
    console.log('Rating input:', ratingInput);
    console.log('Review form:', reviewForm);

    // Hviezdicky click handler
    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            console.log('Star clicked:', value);
            if (ratingInput) {
                ratingInput.value = value;
            }
            // Update vizual
            stars.forEach(function(s) {
                const sVal = parseInt(s.getAttribute('data-value'));
                s.classList.remove('bi-star', 'bi-star-fill');
                s.classList.add(sVal <= value ? 'bi-star-fill' : 'bi-star');
            });
        });
    });

    // Form submit handler
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            console.log('Rating value:', ratingInput ? ratingInput.value : 'no input');

            if (!ratingInput || !ratingInput.value || ratingInput.value < 1) {
                alert('Vyberte prosím hodnotenie (1-5 hviezdičiek)');
                return;
            }

            const formData = new FormData(reviewForm);

            fetch('?c=Accommodation&a=storeReview', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    alert('Recenzia bola pridaná!');
                    location.reload();
                } else {
                    alert('Chyba: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Nastala chyba pri odosielaní');
            });
        });
    }
});
</script>
                    <?php elseif ($canReview && $hasReviewed): ?>
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle"></i> Toto ubytovanie ste už hodnotili.
                        </div>
                    <?php elseif (!$canReview): ?>
                        <div class="alert alert-secondary mb-4">
                            <i class="bi bi-person"></i> Pre pridanie hodnotenia sa <a href="<?= $link->url('auth.login') ?>">prihláste</a>.
                        </div>
                    <?php endif; ?>

                    <!-- Zoznam recenzií -->
                    <?php
                    $isAdmin = $user->isLoggedIn() && \App\Models\User::getOne($user->getId())?->isAdmin();
                    ?>
                    <div id="reviewsContainer">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="mb-3 pb-3 border-bottom" id="review-<?= $review->id ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($review->getUser()?->meno ?? 'Používateľ') ?></strong>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $review->hodnotenie ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted"><?= $review->created_at ? date('d.m.Y', strtotime($review->created_at)) : date('d.m.Y') ?></small>
                                            <?php if ($isAdmin): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteReview(<?= $review->id ?>, <?= $accommodation->id ?>)"
                                                        title="Vymazať recenziu">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($review->komentar): ?>
                                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($review->komentar)) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Zatiaľ žiadne hodnotenia. Buďte prvý!</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($isAdmin): ?>
                    <script>
                    function deleteReview(reviewId, accommodationId) {
                        if (!confirm('Naozaj chcete vymazať túto recenziu?')) return;

                        fetch('?c=Accommodation&a=deleteReview', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'review_id=' + reviewId + '&accommodation_id=' + accommodationId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('review-' + reviewId).remove();
                                alert('Recenzia bola vymazaná');
                            } else {
                                alert('Chyba: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Nastala chyba pri mazaní');
                        });
                    }
                    </script>
                    <?php endif; ?>
                </div>
            </div>

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

            <!-- Rezervačný box -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Rezervácia</h5>
                </div>
                <div class="card-body text-center">
                    <p class="h3 text-primary mb-2"><?= number_format($accommodation->cena_za_noc, 2, ',', ' ') ?> &euro;</p>
                    <p class="text-muted mb-3">za noc</p>
                    <p class="mb-1"><i class="bi bi-people"></i> Kapacita: <?= $accommodation->kapacita ?> osôb</p>
                    <hr>
                    <a href="<?= $link->url('reservation.create', ['id' => $accommodation->id]) ?>"
                       class="btn btn-success btn-lg w-100">
                        <i class="bi bi-calendar-plus"></i> Rezervovať teraz
                    </a>
                    <small class="text-muted d-block mt-2">Rýchla a jednoduchá rezervácia</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= $link->url('accommodation.index') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Späť na zoznam ubytovaní
        </a>
    </div>
</div>

<style>
.text-justify {
    text-align: justify;
}
</style>
