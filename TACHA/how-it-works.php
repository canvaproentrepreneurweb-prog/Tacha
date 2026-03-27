<?php
require_once __DIR__ . '/config/auth.php';

$baseUrl = '';
$assetPrefix = '';

$step = strtolower(trim($_GET['step'] ?? 'paie'));
$steps = [
    'paie' => [
        'title' => 'PAIE',
        'image' => image_path('1PAI_chibi.png', '1PAI_chibi.PNG'),
        'text' => 'Choisissez votre evenement et validez votre place en quelques secondes depuis votre mobile.'
    ],
    'scanne' => [
        'title' => 'SCANNE',
        'image' => image_path('2Scanne_chibi.png', '2Scanne_chibi.PNG'),
        'text' => 'Apres paiement, vous recevez un ticket numerique avec un token unique et un QR local.'
    ],
    'entre' => [
        'title' => 'ENTRE',
        'image' => image_path('3Entre_chibi.png', '3Entre_chibi.PNG'),
        'text' => 'Le staff valide votre token a l entree. Si le ticket est valide, vous entrez immediatement.'
    ],
];

if (!isset($steps[$step])) {
    $step = 'paie';
}

$current = $steps[$step];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Comment ca marche - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container" style="max-width:900px;">
        <h1 class="h2 fw-bold mb-3 text-center">Comment ca marche ?</h1>

        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
            <a class="btn btn-sm <?= $step === 'paie' ? 'btn-tacha-primary' : 'btn-outline-primary' ?>" href="?step=paie">1. Paie</a>
            <a class="btn btn-sm <?= $step === 'scanne' ? 'btn-tacha-primary' : 'btn-outline-primary' ?>" href="?step=scanne">2. Scanne</a>
            <a class="btn btn-sm <?= $step === 'entre' ? 'btn-tacha-primary' : 'btn-outline-primary' ?>" href="?step=entre">3. Entre</a>
        </div>

        <div class="card form-card p-4 p-md-5 text-center">
            <h2 class="h3 mb-3"><?= e($current['title']) ?></h2>
            <div class="how-media mx-auto mb-4" style="max-width:420px;">
                <img src="<?= e($current['image']) ?>" alt="<?= e($current['title']) ?>">
            </div>
            <p class="lead mb-4"><?= e($current['text']) ?></p>
            <a href="events.php" class="btn btn-tacha-primary">Voir les evenements</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

