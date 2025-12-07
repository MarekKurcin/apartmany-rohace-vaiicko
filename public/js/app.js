/**
 * Apartmány pod Roháčmi - JavaScript
 * Validácia formulárov na strane klienta + netriviálny JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 1. VALIDÁCIA FORMULÁROV NA STRANE KLIENTA
    // ========================================
    
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
    
    // ========================================
    // 2. NETRIVIÁLNY JAVASCRIPT
    // ========================================
    
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
    
    // 2.5 Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
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

// ========================================
// VALIDAČNÉ FUNKCIE
// ========================================

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

// ========================================
// POMOCNÉ FUNKCIE
// ========================================

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

// ========================================
// INTERAKTÍVNE FUNKCIE
// ========================================

// Filtrovanie v reálnom čase
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
