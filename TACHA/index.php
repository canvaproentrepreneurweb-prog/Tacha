<?php
require_once __DIR__ . '/config/auth.php';

$baseUrl = '';
$assetPrefix = '';
$hero = image_path('Baniere_accuil.png');
$user = current_user();

$stmt = db()->query('SELECT id, title, city, venue, event_date, event_time, price, image_path FROM events WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3');
$featuredEvents = $stmt->fetchAll();

$createEventLink = (!$user || $user['role'] !== 'organizer') ? 'org/login.php' : 'org/event-create.php';

$howSteps = [
    ['slug' => 'paie', 'image' => image_path('1PAI_chibi.png', '1PAI_chibi.PNG'), 'title' => 'PAIE', 'text' => 'Achete ton billet en ligne'],
    ['slug' => 'scanne', 'image' => image_path('2Scanne_chibi.png', '2Scanne_chibi.PNG'), 'title' => 'SCANNE', 'text' => 'Recois ton QR code unique'],
    ['slug' => 'entre', 'image' => image_path('3Entre_chibi.png', '3Entre_chibi.PNG'), 'title' => 'ENTRE', 'text' => 'Scanne et accede a l evenement']
];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tacha - Accueil</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main>
    <section class="hero-section" style="background-image:linear-gradient(rgba(11,31,58,.62),rgba(11,31,58,.50)),url('<?= e($hero) ?>');">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title">Achete ton billet.<br>Scanne. Entre. Simple.</h1>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="events.php" class="btn btn-tacha-primary">Voir les Evenements</a>
                        <a href="<?= e($createEventLink) ?>" class="btn btn-tacha-green">Creer un Evenement</a>
                    </div>
                </div>
                <div class="col-lg-5"></div>
            </div>
        </div>
    </section>

    <section class="how-section">
        <div class="container">
            <h2 class="section-title"><span class="dot">•</span> Comment ca marche ? <span class="dot">•</span></h2>
            <div class="row g-4">
                <?php foreach ($howSteps as $step): ?>
                    <div class="col-md-4">
                        <a href="how-it-works.php?step=<?= e($step['slug']) ?>" class="how-link">
                            <div class="card how-card">
                                <div class="how-media">
                                    <img src="<?= e($step['image']) ?>" alt="<?= e($step['title']) ?>" class="card-img-top">
                                </div>
                                <div class="card-body">
                                    <h3 class="how-step mb-2"><?= e($step['title']) ?></h3>
                                    <p class="mb-0"><?= e($step['text']) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4 mt-lg-5">
                <a href="events.php" class="btn btn-tacha-primary">Voir Tous les Evenements</a>
            </div>
        </div>
    </section>

    <section class="content-section pb-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h3 fw-bold mb-0">Evenements en vedette</h2>
                <a href="events.php" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="row g-4">
                <?php foreach ($featuredEvents as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card featured-card">
                            <img src="<?= e($event['image_path']) ?>" class="card-img-top" alt="<?= e($event['title']) ?>">
                            <div class="card-body">
                                <span class="badge badge-city mb-2"><?= e($event['city']) ?></span>
                                <h3 class="h5"><?= e($event['title']) ?></h3>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> <?= e($event['venue']) ?></p>
                                <p class="mb-2"><i class="bi bi-calendar-event"></i> <?= e(date('d/m/Y', strtotime($event['event_date']))) ?> - <?= e(substr($event['event_time'], 0, 5)) ?></p>
                                <p class="price-tag mb-3"><?= e(format_fcfa((int) $event['price'])) ?></p>
                                <a class="btn btn-tacha-primary" href="event.php?id=<?= (int) $event['id'] ?>">Voir details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-section" style="background-image:linear-gradient(rgba(11,31,58,.62),rgba(11,31,58,.55)),url('<?= e($hero) ?>');">
        <div class="container text-center">
            <h2 class="cta-title">Rejoins Tacha des maintenant !</h2>
            <p class="cta-text">Simplifie ta vie d'evenemen en quelques clics.</p>
            <a href="events.php" class="btn btn-tacha-green">Creer un Compte</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

