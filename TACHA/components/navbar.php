<?php
$user = current_user();
$assetPrefix = $assetPrefix ?? '';
$logoSrc = $assetPrefix . image_path('logo_tacha.png', 'logo_tacha.PNG');

$eventsLink = ($user && $user['role'] === 'organizer') ? 'org/events.php' : 'events.php';
$organizerLink = ($user && $user['role'] === 'organizer') ? 'org/dashboard.php' : 'org/login.php';
$ticketsLink = $user ? 'my_tickets.php' : 'login.php';
?>
<nav class="navbar navbar-expand-lg sticky-top tacha-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= e($baseUrl) ?>index.php">
            <img src="<?= e($logoSrc) ?>" alt="Tacha" class="tacha-logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tachaNavbar" aria-controls="tachaNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="tachaNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) . e($eventsLink) ?>">Evenements</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) . e($ticketsLink) ?>">Mes Tickets</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) . e($organizerLink) ?>">Organisateur</a></li>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) ?>admin/dashboard.php">Admin</a></li>
                <?php endif; ?>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) ?>logout.php">Deconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e($baseUrl) ?>login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2 ms-lg-3 nav-actions">
                <span class="icon-btn"><i class="bi bi-search"></i></span>
                <a class="icon-btn" href="<?= e($baseUrl) . e($ticketsLink) ?>"><i class="bi bi-person"></i></a>
            </div>
        </div>
    </div>
</nav>

