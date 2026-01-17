document.addEventListener('DOMContentLoaded', function() {

    // Validácia formulára ubytovania
    const accommodationForm = document.getElementById('accommodationForm');
    if (accommodationForm) {
        accommodationForm.addEventListener('submit', function(e) {
            if (!validateAccommodationForm()) {
                e.preventDefault();
            }
        });
        
        // Live validácia
        accommodationForm.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    }
    
    // Validácia formulára atrakcií
    const attractionForm = document.getElementById('attractionForm');
    if (attractionForm) {
        attractionForm.addEventListener('submit', function(e) {
            if (!validateAttractionForm()) {
                e.preventDefault();
            }
        });
        
        attractionForm.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }
    
    // Validácia prihlasovacieho formulára
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Validácia registračného formulára
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
        
        // Password strength indicator
        const passwordInput = document.getElementById('heslo');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength(this.value);
            });
        }
        
        // Kontrola zhody hesiel
        const confirmPassword = document.getElementById('heslo_potvrdenie');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                checkPasswordMatch();
            });
        }
    }
    
    // Validácia rezervačného formulára
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            if (!validateReservationForm()) {
                e.preventDefault();
            }
        });
        
        // Nastavenie minimálneho dátumu (dnes)
        const today = new Date().toISOString().split('T')[0];
        const dateFrom = document.getElementById('datum_od');
        const dateTo = document.getElementById('datum_do');
        
        if (dateFrom) {
            dateFrom.setAttribute('min', today);
            dateFrom.addEventListener('change', function() {
                if (dateTo) {
                    dateTo.setAttribute('min', this.value);
                }
            });
        }
    }
    

    
    // 2.1 Smooth scroll pre anchor linky
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // 2.2 Navbar scroll effect
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar-custom');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (navbar) {
            if (currentScroll > 100) {
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
            }
        }
        
        lastScroll = currentScroll;
    });
    
    // 2.3 Lazy loading pre obrázky
    const lazyImages = document.querySelectorAll('img[data-src]');
    if (lazyImages.length > 0) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // 2.4 Animácie pri scrollovaní (fade in)
    const animateOnScroll = document.querySelectorAll('.accommodation-card, .attraction-card, .feature-card');
    
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    animateOnScroll.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        scrollObserver.observe(el);
    });
    
    // 2.5 Auto-hide alerts (only success/danger notifications, not static info/secondary boxes)
    const notificationAlerts = document.querySelectorAll('.alert-success, .alert-danger');
    notificationAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // 2.6 Character counter pre textareas
    document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0 / ${maxLength}`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            counter.textContent = `${this.value.length} / ${maxLength}`;
        });
    });

});


function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    // Required check
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
    }
    
    // Min length check
    if (field.hasAttribute('minlength')) {
        const minLength = parseInt(field.getAttribute('minlength'));
        if (value.length < minLength) {
            isValid = false;
        }
    }
    
    // Email check
    if (field.type === 'email' && value !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
        }
    }
    
    // Number range check
    if (field.type === 'number') {
        const numValue = parseFloat(value);
        if (field.hasAttribute('min') && numValue < parseFloat(field.getAttribute('min'))) {
            isValid = false;
        }
        if (field.hasAttribute('max') && numValue > parseFloat(field.getAttribute('max'))) {
            isValid = false;
        }
    }
    
    // Update UI
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

function validateAccommodationForm() {
    const form = document.getElementById('accommodationForm');
    let isValid = true;
    
    const nazov = form.querySelector('#nazov');
    const adresa = form.querySelector('#adresa');
    const kapacita = form.querySelector('#kapacita');
    const cena = form.querySelector('#cena_za_noc');
    
    if (!validateField(nazov)) isValid = false;
    if (!validateField(adresa)) isValid = false;
    if (!validateField(kapacita)) isValid = false;
    if (!validateField(cena)) isValid = false;
    
    return isValid;
}

function validateAttractionForm() {
    const form = document.getElementById('attractionForm');
    let isValid = true;
    
    const nazov = form.querySelector('#nazov');
    const typ = form.querySelector('#typ');
    
    if (!validateField(nazov)) isValid = false;
    if (!validateField(typ)) isValid = false;
    
    return isValid;
}

function validateLoginForm() {
    const form = document.getElementById('loginForm');
    let isValid = true;
    
    const email = form.querySelector('#email');
    const heslo = form.querySelector('#heslo');
    
    if (!validateField(email)) isValid = false;
    if (!validateField(heslo)) isValid = false;
    
    return isValid;
}

function validateRegisterForm() {
    const form = document.getElementById('registerForm');
    let isValid = true;
    
    const fields = ['meno', 'priezvisko', 'email', 'heslo', 'heslo_potvrdenie'];
    
    fields.forEach(fieldName => {
        const field = form.querySelector(`#${fieldName}`);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    // Check password match
    const heslo = form.querySelector('#heslo');
    const hesloPotvrdenie = form.querySelector('#heslo_potvrdenie');
    
    if (heslo && hesloPotvrdenie && heslo.value !== hesloPotvrdenie.value) {
        hesloPotvrdenie.classList.add('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

function validateReservationForm() {
    const dateFrom = document.getElementById('datum_od');
    const dateTo = document.getElementById('datum_do');
    let isValid = true;
    
    if (!dateFrom.value) {
        dateFrom.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!dateTo.value) {
        dateTo.classList.add('is-invalid');
        isValid = false;
    }
    
    if (dateFrom.value && dateTo.value) {
        const from = new Date(dateFrom.value);
        const to = new Date(dateTo.value);
        
        if (to <= from) {
            dateTo.classList.add('is-invalid');
            isValid = false;
        }
    }
    
    return isValid;
}


function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function updatePasswordStrength(password) {
    const strengthEl = document.getElementById('passwordStrength');
    if (!strengthEl) return;
    
    let strength = 0;
    
    // Length check
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    
    // Complexity checks
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    strengthEl.className = 'password-strength mt-2';
    
    if (password.length === 0) {
        strengthEl.style.display = 'none';
    } else if (strength < 3) {
        strengthEl.style.display = 'block';
        strengthEl.classList.add('weak');
    } else if (strength < 5) {
        strengthEl.style.display = 'block';
        strengthEl.classList.add('medium');
    } else {
        strengthEl.style.display = 'block';
        strengthEl.classList.add('strong');
    }
}

function checkPasswordMatch() {
    const heslo = document.getElementById('heslo');
    const hesloPotvrdenie = document.getElementById('heslo_potvrdenie');
    
    if (heslo && hesloPotvrdenie) {
        if (heslo.value === hesloPotvrdenie.value && hesloPotvrdenie.value !== '') {
            hesloPotvrdenie.classList.remove('is-invalid');
            hesloPotvrdenie.classList.add('is-valid');
        } else if (hesloPotvrdenie.value !== '') {
            hesloPotvrdenie.classList.remove('is-valid');
            hesloPotvrdenie.classList.add('is-invalid');
        }
    }
}

function confirmDelete(id, type) {
    const typeNames = {
        'accommodation': 'ubytovanie',
        'attraction': 'atrakciu',
        'user': 'používateľa'
    };
    
    if (confirm(`Naozaj chcete vymazať toto ${typeNames[type]}? Táto akcia sa nedá vrátiť späť.`)) {
        window.location.href = `index.php?page=${type}s&action=delete&id=${id}`;
    }
}


function filterCards(searchInput, cardsSelector) {
    const searchValue = searchInput.value.toLowerCase();
    const cards = document.querySelectorAll(cardsSelector);
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        const parent = card.closest('.col-md-6, .col-lg-4');
        
        if (text.includes(searchValue)) {
            if (parent) parent.style.display = '';
        } else {
            if (parent) parent.style.display = 'none';
        }
    });
}

// Zoradenie kariet
function sortCards(sortBy, order = 'asc') {
    const container = document.querySelector('.row.g-4');
    if (!container) return;

    const cards = Array.from(container.children);

    cards.sort((a, b) => {
        let valueA, valueB;

        switch (sortBy) {
            case 'price':
                valueA = parseFloat(a.querySelector('.price-badge')?.textContent?.replace(/[^0-9.]/g, '') || 0);
                valueB = parseFloat(b.querySelector('.price-badge')?.textContent?.replace(/[^0-9.]/g, '') || 0);
                break;
            case 'name':
                valueA = a.querySelector('.card-title')?.textContent?.toLowerCase() || '';
                valueB = b.querySelector('.card-title')?.textContent?.toLowerCase() || '';
                break;
            default:
                return 0;
        }

        if (order === 'asc') {
            return valueA > valueB ? 1 : -1;
        } else {
            return valueA < valueB ? 1 : -1;
        }
    });

    cards.forEach(card => container.appendChild(card));
}

// ===========================================
// AJAX FUNKCIE
// ===========================================

/**
 * AJAX Filtrovanie ubytovani
 * Dynamicke nacitanie ubytovani bez refreshu stranky
 */
function initAjaxFilter() {
    const filterForm = document.getElementById('accommodationFilterForm');
    if (!filterForm) return;

    const accommodationGrid = document.getElementById('accommodationGrid');

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        filterAccommodations();
    });

    // Live filtrovanie pri zmene selectov a checkboxov
    let debounceTimer;
    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => filterAccommodations(), 300);
        });
    });

    // Checkboxy pre vybavenie - okamžité filtrovanie
    filterForm.querySelectorAll('.vybavenie-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => filterAccommodations(), 300);
        });
    });

    function filterAccommodations() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        formData.forEach((value, key) => {
            if (value) params.append(key, value);
        });

        const url = `index.php?c=Accommodation&a=filterAjax&${params.toString()}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAccommodations(data.data, data.count);
            } else {
                showFilterError('Nastala chyba pri načítavaní');
            }
        })
        .catch(error => {
            console.error('AJAX Filter Error:', error);
            showFilterError('Nastala chyba pri komunikácii so serverom');
        });
    }

    function renderAccommodations(accommodations, count) {
        if (!accommodationGrid) return;

        // Aktualizacia poctu vysledkov s gramaticky správnym textom
        const countBadge = document.getElementById('resultCount');
        const resultBadge = document.getElementById('resultBadge');
        if (countBadge) {
            countBadge.textContent = count;
        }
        if (resultBadge) {
            let text = 'ubytovaní';
            if (count == 1) text = 'ubytovanie';
            else if (count >= 2 && count <= 4) text = 'ubytovania';
            resultBadge.innerHTML = `<i class="bi bi-building"></i> <span id="resultCount">${count}</span> ${text}`;
        }

        if (accommodations.length === 0) {
            accommodationGrid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="bi bi-info-circle"></i>
                        Nenasli sa ziadne ubytovania podla zadanych kriterii.
                        <br>
                        <a href="javascript:void(0)" onclick="clearFilters()" class="alert-link">Zrusit filtre</a>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        accommodations.forEach(acc => {
            const vybavenieHtml = acc.vybavenie.slice(0, 3).map(v =>
                `<span class="badge bg-secondary me-1">${escapeHtml(v)}</span>`
            ).join('');
            const extraCount = acc.vybavenie.length > 3 ?
                `<span class="badge bg-light text-dark">+${acc.vybavenie.length - 3}</span>` : '';

            html += `
                <div class="col-md-6 col-lg-4 accommodation-item" data-aos="fade-up">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="${escapeHtml(acc.obrazok)}"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 alt="${escapeHtml(acc.nazov)}"
                                 loading="lazy">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-primary fs-6">
                                ${acc.cena_za_noc} €/noc
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">${escapeHtml(acc.nazov)}</h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> ${escapeHtml(acc.adresa)}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-people"></i> Kapacita: ${acc.kapacita} osob
                            </p>
                            ${acc.popis ? `<p class="card-text small">${escapeHtml(acc.popis)}</p>` : ''}
                            <div class="mb-3">
                                ${vybavenieHtml}
                                ${extraCount}
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="?c=Accommodation&a=show&id=${acc.id}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Zobrazit detail
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });

        accommodationGrid.innerHTML = html;

        // Animacia novych kariet
        accommodationGrid.querySelectorAll('.accommodation-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    function showFilterError(message) {
        if (!accommodationGrid) return;
        accommodationGrid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> ${message}
                </div>
            </div>
        `;
    }
}

function clearFilters() {
    const filterForm = document.getElementById('accommodationFilterForm');
    if (filterForm) {
        // Reset všetkých inputov
        filterForm.reset();

        // Explicitne resetuj selecty na prvú hodnotu
        filterForm.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });

        // Odškrtni všetky checkboxy
        filterForm.querySelectorAll('.vybavenie-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Spusti filtrovanie
        filterForm.dispatchEvent(new Event('submit'));
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * AJAX Pridavanie recenzii
 * Dynamicke pridanie recenzie bez refreshu stranky
 */
function initAjaxReview() {
    const reviewForm = document.getElementById('reviewForm');
    if (!reviewForm) return;

    const reviewsContainer = document.getElementById('reviewsContainer');
    const reviewsHeader = document.getElementById('reviewsHeader');
    const ratingStars = reviewForm.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('reviewRating');

    // Interaktivne hviezdicky
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.dataset.value;
            ratingInput.value = value;
            updateStars(value);
        });

        star.addEventListener('mouseenter', function() {
            const value = this.dataset.value;
            highlightStars(value);
        });

        star.addEventListener('mouseleave', function() {
            highlightStars(ratingInput.value || 0);
        });
    });

    function updateStars(value) {
        ratingStars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.classList.remove('bi-star', 'bi-star-fill');
            star.classList.add(starValue <= value ? 'bi-star-fill' : 'bi-star');
        });
    }

    function highlightStars(value) {
        ratingStars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            star.classList.remove('bi-star', 'bi-star-fill');
            star.classList.add(starValue <= value ? 'bi-star-fill' : 'bi-star');
        });
    }

    // Odoslanie formulara
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(reviewForm);
        const submitBtn = reviewForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Validacia
        if (!ratingInput.value || ratingInput.value < 1) {
            showReviewAlert('Vyberte prosim hodnotenie (1-5 hviezdicky)', 'warning');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Odosielam...';

        fetch('index.php?c=Accommodation&a=storeReview', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            if (data.success) {
                // Pridat novu recenziu do zoznamu
                addReviewToList(data.review);

                // Aktualizovat priemerne hodnotenie
                updateAverageRating(data.newAverage, data.reviewCount);

                // Resetovat formular
                reviewForm.reset();
                ratingInput.value = '';
                updateStars(0);

                // Skryt formular (uzivatel uz hodnotil)
                reviewForm.style.display = 'none';

                showReviewAlert(data.message, 'success');
            } else {
                showReviewAlert(data.error, 'danger');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            console.error('AJAX Review Error:', error);
            showReviewAlert('Nastala chyba pri odosielani recenzie', 'danger');
        });
    });

    function addReviewToList(review) {
        if (!reviewsContainer) return;

        // Ak je prazdny zoznam, odstranit placeholder
        const emptyMessage = reviewsContainer.querySelector('.text-muted');
        if (emptyMessage) emptyMessage.remove();

        const starsHtml = Array(5).fill(0).map((_, i) =>
            `<i class="bi bi-star${i < review.hodnotenie ? '-fill' : ''}"></i>`
        ).join('');

        const reviewHtml = `
            <div class="mb-3 pb-3 border-bottom review-item" style="animation: fadeIn 0.5s ease">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${escapeHtml(review.user_name)}</strong>
                        <div class="text-warning">${starsHtml}</div>
                    </div>
                    <small class="text-muted">${review.created_at}</small>
                </div>
                ${review.komentar ? `<p class="mt-2 mb-0">${escapeHtml(review.komentar)}</p>` : ''}
            </div>
        `;

        reviewsContainer.insertAdjacentHTML('afterbegin', reviewHtml);
    }

    function updateAverageRating(newAverage, reviewCount) {
        const ratingDisplay = document.getElementById('averageRating');
        if (ratingDisplay && newAverage) {
            const starsHtml = Array(5).fill(0).map((_, i) => {
                if (i + 1 <= newAverage) return '<i class="bi bi-star-fill"></i>';
                if (i + 0.5 <= newAverage) return '<i class="bi bi-star-half"></i>';
                return '<i class="bi bi-star"></i>';
            }).join('');

            ratingDisplay.innerHTML = `
                <span class="text-warning">${starsHtml}</span>
                <small class="text-muted">(${newAverage.toFixed(1)} z ${reviewCount} hodnoteni)</small>
            `;
        }

        // Aktualizovat header
        if (reviewsHeader) {
            reviewsHeader.innerHTML = `<i class="bi bi-star"></i> Hodnotenia (${reviewCount})`;
        }
    }

    function showReviewAlert(message, type) {
        const alertContainer = document.getElementById('reviewAlerts');
        if (!alertContainer) return;

        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHtml;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    }
}

// Inicializacia AJAX funkcii na konci suboru
document.addEventListener('DOMContentLoaded', function() {
    initAjaxFilter();
    initAjaxReview();
    initAttractionFilter();
});

/**
 * AJAX Filtrovanie atrakcii
 */
function initAttractionFilter() {
    const filterForm = document.getElementById('attractionFilterForm');
    if (!filterForm) return;

    const attractionGrid = document.getElementById('attractionGrid');

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        filterAttractions();
    });

    // Live filtrovanie pri zmene selectov
    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function() {
            filterAttractions();
        });
    });

    function filterAttractions() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        formData.forEach((value, key) => {
            if (value) params.append(key, value);
        });

        const url = `index.php?c=Attraction&a=filterAjax&${params.toString()}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAttractions(data.data, data.count);
            }
        })
        .catch(error => {
            console.error('AJAX Attraction Filter Error:', error);
        });
    }

    function renderAttractions(attractions, count) {
        if (!attractionGrid) return;

        // Aktualizacia poctu vysledkov
        const resultBadge = document.getElementById('resultBadge');
        if (resultBadge) {
            let text = 'atrakcií';
            if (count == 1) text = 'atrakcia';
            else if (count >= 2 && count <= 4) text = 'atrakcie';
            resultBadge.innerHTML = `<i class="bi bi-pin-map"></i> <span id="resultCount">${count}</span> ${text}`;
        }

        if (attractions.length === 0) {
            attractionGrid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="bi bi-info-circle"></i>
                        Nenašli sa žiadne atrakcie podľa zadaných kritérií.
                        <br>
                        <a href="javascript:void(0)" onclick="clearAttractionFilters()" class="alert-link">Zrušiť filtre</a>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        attractions.forEach(attr => {
            const cenaHtml = attr.is_free
                ? '<strong class="text-success">Zadarmo</strong>'
                : escapeHtml(attr.cena_formatted);

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="${escapeHtml(attr.obrazok)}"
                                 class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 alt="${escapeHtml(attr.nazov)}"
                                 loading="lazy">
                            ${attr.typ ? `<span class="position-absolute top-0 end-0 m-2 badge bg-info">${escapeHtml(attr.typ)}</span>` : ''}
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">${escapeHtml(attr.nazov)}</h5>
                            ${attr.poloha ? `<p class="text-muted mb-2"><i class="bi bi-geo-alt"></i> ${escapeHtml(attr.poloha)}</p>` : ''}
                            <p class="text-muted mb-2"><i class="bi bi-tag"></i> ${cenaHtml}</p>
                            ${attr.popis ? `<p class="card-text small">${escapeHtml(attr.popis)}</p>` : ''}
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="index.php?c=Attraction&a=show&id=${attr.id}" class="btn btn-outline-info w-100">
                                <i class="bi bi-eye"></i> Zobraziť detail
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });

        attractionGrid.innerHTML = html;
    }
}

function clearAttractionFilters() {
    const filterForm = document.getElementById('attractionFilterForm');
    if (filterForm) {
        filterForm.reset();
        filterForm.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
        filterForm.dispatchEvent(new Event('submit'));
    }
}
