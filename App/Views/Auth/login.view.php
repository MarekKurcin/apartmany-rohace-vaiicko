<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array|null $errors */
/** @var array|null $old */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card auth-card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle auth-icon"></i>
                        <h3>Prihlásenie</h3>
                    </div>

                    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
                        <div class="alert alert-success">
                            Registrácia prebehla úspešne. Teraz sa môžete prihlásiť.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors['login'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errors['login']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= $link->url('auth.loginPost') ?>" method="POST" id="loginForm" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" 
                                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    <?= $errors['email'] ?? 'Zadajte platný email' ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="heslo" class="form-label">Heslo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control <?= !empty($errors['heslo']) ? 'is-invalid' : '' ?>" 
                                       id="heslo" name="heslo" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('heslo')">
                                    <i class="bi bi-eye" id="heslo-icon"></i>
                                </button>
                                <div class="invalid-feedback">
                                    <?= $errors['heslo'] ?? 'Zadajte heslo' ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Prihlásiť sa
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Nemáte účet? 
                            <a href="<?= $link->url('auth.register') ?>">Zaregistrujte sa</a>
                        </p>
                    </div>

                    <hr class="my-4">
                    
                    <div class="alert alert-info small">
                        <strong>Testovacie účty:</strong><br>
                        Admin: admin@apartmany.sk / password<br>
                        Ubytovateľ: ubytovatel@test.sk / password<br>
                        Turista: turista@test.sk / password
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
