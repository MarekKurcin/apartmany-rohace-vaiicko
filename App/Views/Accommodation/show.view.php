<?php
$allImages = $accommodation->getAllImages();
if (empty($allImages)) {
    $allImages = ['https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200'];
}
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
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <!-- Galéria obrázkov -->
                <?php if (count($allImages) > 1): ?>
                    <div id="galleryCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-indicators">
                            <?php foreach ($allImages as $index => $img): ?>
                                <button type="button" data-bs-target="#galleryCarousel" data-bs-slide-to="<?= $index ?>"
                                        <?= $index === 0 ? 'class="active" aria-current="true"' : '' ?>></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($allImages as $index => $img): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($img) ?>"
                                         class="d-block w-100"
                                         style="height: 400px; object-fit: cover; cursor: pointer;"
                                         alt="<?= htmlspecialchars($accommodation->nazov) ?>"
                                         onclick="openLightbox(<?= $index ?>)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>

                    <!-- Miniatúry -->
                    <div class="d-flex gap-2 p-2 bg-light">
                        <?php foreach ($allImages as $index => $img): ?>
                            <img src="<?= htmlspecialchars($img) ?>"
                                 class="gallery-thumb"
                                 style="width: 80px; height: 60px; object-fit: cover; cursor: pointer; border-radius: 4px; opacity: <?= $index === 0 ? '1' : '0.6' ?>;"
                                 onclick="goToSlide(<?= $index ?>)"
                                 data-index="<?= $index ?>">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($allImages[0]) ?>"
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
            <?php
            $canEdit = false;
            if ($user->isLoggedIn()) {
                $currentUser = \App\Models\User::getOne($user->getId());
                $canEdit = $currentUser && ($currentUser->isAdmin() || $accommodation->user_id == $user->getId());
            }
            ?>
            <?php if ($canEdit): ?>
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
                        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Atrakcie v okolí</h5>
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
                                    <?php if ($attr->vzdialenost_km !== null): ?>
                                        <small class="text-info">
                                            <i class="bi bi-signpost-2"></i> <?= number_format($attr->vzdialenost_km, 1) ?> km od ubytovania
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
                <div class="card-body">
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

            <!-- Kalendár dostupnosti -->
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: var(--primary-color); color: white;">
                    <h5 class="mb-0"><i class="bi bi-calendar"></i> Dostupnosť</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="prevMonth">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="calendarTitle" class="fw-bold">Načítavam...</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="nextMonth">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div id="availabilityCalendar" class="mb-3">
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-hourglass-split"></i> Načítavam kalendár...
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span><span class="d-inline-block rounded" style="width: 12px; height: 12px; background: #e8f5e9;"></span> Voľné</span>
                        <span><span class="d-inline-block rounded" style="width: 12px; height: 12px; background: #ffebee;"></span> Obsadené</span>
                        <span><span class="d-inline-block rounded" style="width: 12px; height: 12px; background: #fff3e0;"></span> Čakajúce</span>
                    </div>
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
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}
.calendar-header {
    text-align: center;
    font-weight: bold;
    font-size: 0.75rem;
    padding: 5px;
    color: #666;
}
.calendar-day {
    text-align: center;
    padding: 8px 4px;
    font-size: 0.85rem;
    border-radius: 4px;
    background: #e8f5e9;
    color: #2d6a4f;
}
.calendar-day.booked {
    background: #ffebee;
    color: #c62828;
}
.calendar-day.pending {
    background: #fff3e0;
    color: #e65100;
}
.calendar-day.empty {
    background: transparent;
}
.calendar-day.today {
    border: 2px solid #2d6a4f;
    font-weight: bold;
}
.gallery-thumb {
    transition: opacity 0.2s;
}
.gallery-thumb:hover {
    opacity: 1 !important;
}
.lightbox {
    display: none;
    position: fixed;
    z-index: 9999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.95);
}
.lightbox.active {
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
}
.lightbox-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}
.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 50px;
    cursor: pointer;
    padding: 20px;
}
.lightbox-prev { left: 20px; }
.lightbox-next { right: 20px; }
</style>

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <span class="lightbox-nav lightbox-prev" onclick="lightboxPrev()">&#10094;</span>
    <img id="lightbox-img" src="">
    <span class="lightbox-nav lightbox-next" onclick="lightboxNext()">&#10095;</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accommodationId = <?= $accommodation->id ?>;
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth() + 1;

    const monthNames = ['Január', 'Február', 'Marec', 'Apríl', 'Máj', 'Jún',
                        'Júl', 'August', 'September', 'Október', 'November', 'December'];
    const dayNames = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];

    function loadCalendar() {
        const titleEl = document.getElementById('calendarTitle');
        const calendarEl = document.getElementById('availabilityCalendar');

        if (!titleEl || !calendarEl) return;

        titleEl.textContent = monthNames[currentMonth - 1] + ' ' + currentYear;
        calendarEl.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-hourglass-split"></i> Načítavam...</div>';

        const url = window.location.pathname + '?c=Accommodation&a=getAvailability&id=' + accommodationId + '&year=' + currentYear + '&month=' + currentMonth;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderCalendar(data.bookedDates || {});
                } else {
                    calendarEl.innerHTML = '<div class="text-center text-danger py-3">Chyba pri načítaní</div>';
                }
            })
            .catch(error => {
                console.error('Error loading calendar:', error);
                renderCalendar({});
            });
    }

    function renderCalendar(bookedDates) {
        const calendarEl = document.getElementById('availabilityCalendar');
        if (!calendarEl) return;

        const firstDay = new Date(currentYear, currentMonth - 1, 1);
        const lastDay = new Date(currentYear, currentMonth, 0);
        const daysInMonth = lastDay.getDate();

        let startDay = firstDay.getDay();
        startDay = startDay === 0 ? 6 : startDay - 1;

        const today = new Date();
        const todayStr = today.getFullYear() + '-' +
                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                        String(today.getDate()).padStart(2, '0');

        let html = '<div class="calendar-grid">';

        dayNames.forEach(day => {
            html += '<div class="calendar-header">' + day + '</div>';
        });

        for (let i = 0; i < startDay; i++) {
            html += '<div class="calendar-day empty"></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = currentYear + '-' +
                           String(currentMonth).padStart(2, '0') + '-' +
                           String(day).padStart(2, '0');

            let classes = 'calendar-day';

            if (bookedDates[dateStr]) {
                if (bookedDates[dateStr] === 'cakajuca') {
                    classes += ' pending';
                } else {
                    classes += ' booked';
                }
            }

            if (dateStr === todayStr) {
                classes += ' today';
            }

            html += '<div class="' + classes + '">' + day + '</div>';
        }

        html += '</div>';
        calendarEl.innerHTML = html;
    }

    document.getElementById('prevMonth')?.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        loadCalendar();
    });

    document.getElementById('nextMonth')?.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        loadCalendar();
    });

    loadCalendar();
});

// Gallery & Lightbox functions
const galleryImages = <?= json_encode($allImages) ?>;
let currentLightboxIndex = 0;

function goToSlide(index) {
    const carousel = document.getElementById('galleryCarousel');
    if (carousel) {
        const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carousel);
        bsCarousel.to(index);
    }
    updateThumbnails(index);
}

function updateThumbnails(activeIndex) {
    document.querySelectorAll('.gallery-thumb').forEach((thumb, i) => {
        thumb.style.opacity = i === activeIndex ? '1' : '0.6';
    });
}

// Listen for carousel slide events
document.getElementById('galleryCarousel')?.addEventListener('slid.bs.carousel', function(e) {
    updateThumbnails(e.to);
});

function openLightbox(index) {
    currentLightboxIndex = index;
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.src = galleryImages[index];
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

function lightboxPrev() {
    currentLightboxIndex = (currentLightboxIndex - 1 + galleryImages.length) % galleryImages.length;
    document.getElementById('lightbox-img').src = galleryImages[currentLightboxIndex];
}

function lightboxNext() {
    currentLightboxIndex = (currentLightboxIndex + 1) % galleryImages.length;
    document.getElementById('lightbox-img').src = galleryImages[currentLightboxIndex];
}

// Close lightbox on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxPrev();
    if (e.key === 'ArrowRight') lightboxNext();
});

// Close lightbox on background click
document.getElementById('lightbox')?.addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
</script>
