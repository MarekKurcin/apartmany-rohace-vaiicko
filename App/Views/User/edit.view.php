<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\User $currentUser */
/** @var array $errors */
/** @var array $old */

$errors = $errors ?? [];
$old = $old ?? [];
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $link->url('home.index') ?>">Domov</a></li>
            <li class="breadcrumb-item"><a href="<?= $link->url('user.profile') ?>">Profil</a></li>
            <li class="breadcrumb-item active">Upraviť profil</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-pencil"></i> Upraviť profil</h4>
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

                    <form method="POST" action="<?= $link->url('user.update') ?>" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($old['email'] ?? $currentUser->email) ?>" 
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meno" class="form-label">Meno *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="meno" 
                                       name="meno" 
                                       value="<?= htmlspecialchars($old['meno'] ?? $currentUser->meno) ?>" 
                                       required 
                                       minlength="2">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priezvisko" class="form-label">Priezvisko *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="priezvisko" 
                                       name="priezvisko" 
                                       value="<?= htmlspecialchars($old['priezvisko'] ?? $currentUser->priezvisko) ?>" 
                                       required 
                                       minlength="2">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telefon" class="form-label">Telefón</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="telefon" 
                                   name="telefon" 
                                   value="<?= htmlspecialchars($old['telefon'] ?? $currentUser->telefon) ?>"
                                   placeholder="+421900000000">
                            <small class="text-muted">Formát: +421XXXXXXXXX</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Uložiť zmeny
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
