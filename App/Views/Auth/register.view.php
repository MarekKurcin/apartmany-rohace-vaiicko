<?php
/** @var \Framework\Support\LinkGenerator $link */
/** @var array|null $errors */
/** @var array|null $old */
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card auth-card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus auth-icon"></i>
                        <h3>Registrácia</h3>
                    </div>

                    <form action="<?= $link->url('auth.registerPost') ?>" method="POST" id="registerForm" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meno" class="form-label">Meno *</label>
                                <input type="text" class="form-control <?= !empty($errors['meno']) ? 'is-invalid' : '' ?>" 
                                       id="meno" name="meno" 
                                       value="<?= htmlspecialchars($old['meno'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    <?= $errors['meno'] ?? '' ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priezvisko" class="form-label">Priezvisko *</label>
                                <input type="text" class="form-control <?= !empty($errors['priezvisko']) ? 'is-invalid' : '' ?>" 
                                       id="priezvisko" name="priezvisko" 
                                       value="<?= htmlspecialchars($old['priezvisko'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    <?= $errors['priezvisko'] ?? '' ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" 
                                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    <?= $errors['email'] ?? '' ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telefon" class="form-label">Telefón</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" 
                                       id="telefon" name="telefon" 
                                       value="<?= htmlspecialchars($old['telefon'] ?? '') ?>" 
                                       placeholder="+421 XXX XXX XXX">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="heslo" class="form-label">Heslo *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control <?= !empty($errors['heslo']) ? 'is-invalid' : '' ?>" 
                                       id="heslo" name="heslo" required minlength="6">
                                <div class="invalid-feedback">
                                    <?= $errors['heslo'] ?? '' ?>
                                </div>
                            </div>
                            <div id="passwordStrength" class="mt-1"></div>
                        </div>

                        <div class="mb-3">
                            <label for="heslo_potvrdenie" class="form-label">Potvrdenie hesla *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control <?= !empty($errors['heslo_potvrdenie']) ? 'is-invalid' : '' ?>" 
                                       id="heslo_potvrdenie" name="heslo_potvrdenie" required>
                                <div class="invalid-feedback">
                                    <?= $errors['heslo_potvrdenie'] ?? '' ?>
                                </div>
                            </div>
                            <div id="passwordMatch" class="mt-1"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus"></i> Zaregistrovať sa
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Už máte účet? 
                            <a href="<?= $link->url('auth.login') ?>">Prihláste sa</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
