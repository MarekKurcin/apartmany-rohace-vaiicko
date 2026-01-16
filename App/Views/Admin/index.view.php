<?php

/** @var \Framework\Support\LinkGenerator $link */
/** @var \App\Models\User $currentUser */
/** @var array $stats */
/** @var array $recentUsers */
/** @var array $recentAccommodations */
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <h1 class="mb-4">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
            </h1>
            <p class="lead">Vitajte, <strong><?= htmlspecialchars($currentUser->meno . ' ' . $currentUser->priezvisko) ?></strong>!</p>
        </div>
    </div>

    <!-- Štatistiky -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Používatelia</h6>
                            <h2 class="mb-0"><?= $stats['totalUsers'] ?></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Ubytovania</h6>
                            <h2 class="mb-0"><?= $stats['totalAccommodations'] ?></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-house-fill" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Atrakcie</h6>
                            <h2 class="mb-0"><?= $stats['totalAttractions'] ?></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-geo-alt-fill" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-0">Rezervácie</h6>
                            <h2 class="mb-0"><?= $stats['totalReservations'] ?></h2>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-calendar-check-fill" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rýchle akcie -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Rýchle akcie</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="<?= $link->url('accommodation.create') ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Pridať ubytovanie
                        </a>
                        <a href="<?= $link->url('attraction.create') ?>" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Pridať atrakciu
                        </a>
                        <a href="<?= $link->url('accommodation.index') ?>" class="btn btn-secondary">
                            <i class="bi bi-list"></i> Všetky ubytovania
                        </a>
                        <a href="<?= $link->url('attraction.index') ?>" class="btn btn-secondary">
                            <i class="bi bi-list"></i> Všetky atrakcie
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Posledný používatelia -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Najnovší používatelia</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentUsers)): ?>
                        <p class="text-muted">Žiadni používatelia</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $u): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($u->meno . ' ' . $u->priezvisko) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($u->email) ?></small>
                                        </div>
                                        <span class="badge bg-<?= $u->rola === 'admin' ? 'danger' : ($u->rola === 'ubytovatel' ? 'warning' : 'secondary') ?>">
                                            <?= htmlspecialchars($u->rola) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Posledné ubytovania -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-house-add"></i> Najnovšie ubytovania</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentAccommodations)): ?>
                        <p class="text-muted">Žiadne ubytovania</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentAccommodations as $acc): ?>
                                <a href="<?= $link->url('accommodation.show', ['id' => $acc->id]) ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($acc->nazov) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($acc->adresa) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success"><?= number_format($acc->cena_za_noc, 2) ?> €</div>
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i> <?= $acc->kapacita ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Celková štatistika recenzií -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-star-fill"></i> Štatistika systému</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-primary"><?= $stats['totalReviews'] ?></h3>
                            <p class="text-muted">Celkový počet recenzií</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-success"><?= $stats['totalReservations'] ?></h3>
                            <p class="text-muted">Celkový počet rezervácií</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info"><?= $stats['totalAccommodations'] + $stats['totalAttractions'] ?></h3>
                            <p class="text-muted">Celkový počet položiek</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
