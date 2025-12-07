<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\User $currentUser */

$success = $_GET['success'] ?? null;
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($success === 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> Profil bol úspešne aktualizovaný
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($success === 'password_changed'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> Heslo bolo úspešne zmenené
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-person-circle"></i> Môj profil</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Email:</div>
                        <div class="col-md-8"><?= htmlspecialchars($currentUser->email) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Meno:</div>
                        <div class="col-md-8"><?= htmlspecialchars($currentUser->meno) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Priezvisko:</div>
                        <div class="col-md-8"><?= htmlspecialchars($currentUser->priezvisko) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Telefón:</div>
                        <div class="col-md-8"><?= htmlspecialchars($currentUser->telefon ?: 'Neuvedené') ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Rola:</div>
                        <div class="col-md-8">
                            <span class="badge bg-<?= $currentUser->rola === 'admin' ? 'danger' : ($currentUser->rola === 'ubytovatel' ? 'warning' : 'secondary') ?>">
                                <?= htmlspecialchars(ucfirst($currentUser->rola)) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Registrovaný:</div>
                        <div class="col-md-8"><?= htmlspecialchars($currentUser->datum_vytvorenia) ?></div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="<?= $link->url('user.edit') ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Upraviť profil
                        </a>
                        <a href="<?= $link->url('user.changePassword') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-key"></i> Zmeniť heslo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
