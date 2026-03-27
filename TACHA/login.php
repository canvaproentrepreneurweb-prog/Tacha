<?php
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$baseUrl = '';
$assetPrefix = '';
$error = '';
$redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php');
if ($redirect === '') {
    $redirect = 'index.php';
}
$redirect = sanitize_redirect($redirect, 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login_user($email, $password)) {
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Identifiants invalides.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container auth-container">
        <div class="auth-shell row g-0">
            <div class="col-lg-6 p-3 p-lg-4 auth-left-col">
                <section class="auth-showcase">
                    <h2 class="auth-showcase-title">Vendez vos evenements et suivez vos entrees en temps reel.</h2>
                    <div class="auth-tags">
                        <span class="auth-tag"><i class="bi bi-ticket-perforated"></i> Billets</span>
                        <span class="auth-tag"><i class="bi bi-qr-code-scan"></i> QR Code</span>
                        <span class="auth-tag"><i class="bi bi-cash-stack"></i> Paiements</span>
                        <span class="auth-tag"><i class="bi bi-bar-chart-line"></i> Statistiques</span>
                        <span class="auth-tag"><i class="bi bi-shield-check"></i> Validation</span>
                    </div>
                </section>
            </div>

            <div class="col-lg-6 p-3 p-lg-4">
                <section class="auth-form-wrap">
                    <h1 class="auth-title mb-2">Connexion</h1>
                    <p class="text-muted mb-4">Accede a ton espace participant, organisateur ou admin.</p>

                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

                    <form method="post" novalidate>
                        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                        <div class="mb-3">
                            <label class="form-label">Adresse email</label>
                            <input type="email" name="email" class="form-control auth-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control auth-input" required>
                        </div>
                        <button class="btn btn-tacha-primary w-100">Se connecter</button>
                    </form>

                    <div class="auth-help mt-4">
                        <small class="text-muted d-block">Test participant: user@tacha.cm / 123456</small>
                        <small class="text-muted d-block">Test organisateur: org@tacha.cm / 123456</small>
                        <small class="text-muted d-block">Test admin: admin@tacha.cm / 123456</small>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

