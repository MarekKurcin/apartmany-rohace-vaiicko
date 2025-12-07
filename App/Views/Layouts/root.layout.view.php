<?php

/** @var string $contentHTML */
/** @var \Framework\Auth\AppUser $user */
/** @var \Framework\Support\LinkGenerator $link */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartmány pod Roháčmi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Vlastné CSS štýly -->
    <link href="<?= $link->asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigácia -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?= $link->url('home.index') ?>">
                <i class="bi bi-house-heart"></i> Apartmány pod Roháčmi
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $link->url('home.index') ?>">Domov</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $link->url('accommodation.index') ?>">Ubytovanie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $link->url('attraction.index') ?>">Atrakcie</a>
                    </li>
                    <?php if ($user->isLoggedIn()) {
                        $currentUser = \App\Models\User::getOne($user->getId());
                        if ($currentUser && $currentUser->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $link->url('admin.index') ?>">
                            <i class="bi bi-gear-fill"></i> Admin
                        </a>
                    </li>
                    <?php endif; } ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($user->isLoggedIn()) { 
                        $currentUser = \App\Models\User::getOne($user->getId());
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?= htmlspecialchars($currentUser->getFullName()) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= $link->url('user.profile') ?>">Môj profil</a></li>
                                <?php if ($currentUser->isUbytovatel()): ?>
                                    <li><a class="dropdown-item" href="<?= $link->url('accommodation.create') ?>">Pridať ubytovanie</a></li>
                                <?php endif; ?>
                                <?php if ($currentUser->isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= $link->url('attraction.create') ?>">Pridať atrakciu</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $link->url('auth.logout') ?>">Odhlásiť sa</a></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $link->url('auth.login') ?>">Prihlásenie</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="<?= $link->url('auth.register') ?>">Registrácia</a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hlavný obsah -->
    <main class="main-content">
        <?= $contentHTML ?>
    </main>

    <!-- Footer -->
    <footer class="footer-custom mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-house-heart"></i> Apartmány pod Roháčmi</h5>
                    <p class="text-secondary">Nájdite si ideálne ubytovanie v krásnom prostredí Západných Tatier.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Rýchle odkazy</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= $link->url('accommodation.index') ?>">Ubytovanie</a></li>
                        <li><a href="<?= $link->url('attraction.index') ?>">Atrakcie</a></li>
                        <li><a href="<?= $link->url('home.contact') ?>">Kontakt</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Kontakt</h5>
                    <ul class="list-unstyled text-secondary">
                        <li><i class="bi bi-geo-alt"></i> Zuberec, Slovensko</li>
                        <li><i class="bi bi-envelope"></i> info@apartmany-rohace.sk</li>
                        <li><i class="bi bi-telephone"></i> +421 900 000 000</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> Apartmány pod Roháčmi. Všetky práva vyhradené.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Vlastný JavaScript -->
    <script src="<?= $link->asset('js/app.js') ?>"></script>
</body>
</html>
