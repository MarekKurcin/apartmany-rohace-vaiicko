<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\User $currentUser */
/** @var array $errors */

$errors = $errors ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $link->url('home.index') ?>">Domov</a></li>
            <li class="breadcrumb-item"><a href="<?= $link->url('user.profile') ?>">Profil</a></li>
            <li class="breadcrumb-item active">Zmena hesla</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-key"></i> Zmena hesla</h4>
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

                    <form method="POST" action="<?= $link->url('user.changePassword') ?>" novalidate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Súčasné heslo *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nové heslo *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="new_password" 
                                   name="new_password" 
                                   required 
                                   minlength="6">
                            <small class="text-muted">Minimálne 6 znakov</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Potvrdenie nového hesla *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
                                   minlength="6">
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <small>Heslo musí obsahovať minimálne 6 znakov.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Zmeniť heslo
                            </button>
                            <a href="<?= $link->url('user.profile') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Zrušiť
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
