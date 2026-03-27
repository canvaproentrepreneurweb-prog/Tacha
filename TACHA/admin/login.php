<?php
require_once __DIR__ . '/../config/auth.php';

if (is_logged_in()) {
    $u = current_user();
    if ($u && $u['role'] === 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}

$baseUrl = '../';
$assetPrefix = '../';
$error = '';
$redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? 'dashboard.php');
if ($redirect === '') {
    $redirect = 'dashboard.php';
}
$redirect = sanitize_redirect($redirect, 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login_user($email, $password)) {
        $u = current_user();
        if ($u && $u['role'] === 'admin') {
            header('Location: ' . $redirect);
            exit;
        }
        logout_user();
        $error = 'Ce compte n a pas les droits administrateur.';
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
    <title>Connexion Admin - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container auth-container">
        <div class="auth-shell row g-0">
            <div class="col-lg-6 p-3 p-lg-4 auth-left-col">
                <section class="auth-showcase">
                    <h2 class="auth-showcase-title">Supervisez toute la plateforme et gardez le controle total.</h2>
                    <div class="auth-tags">
                        <span class="auth-tag"><i class="bi bi-people"></i> Utilisateurs</span>
                        <span class="auth-tag"><i class="bi bi-collection"></i> Evenements</span>
                        <span class="auth-tag"><i class="bi bi-receipt"></i> Tickets</span>
                        <span class="auth-tag"><i class="bi bi-sliders"></i> Actions Admin</span>
                    </div>
                </section>
            </div>

            <div class="col-lg-6 p-3 p-lg-4">
                <section class="auth-form-wrap">
                    <h1 class="auth-title mb-2">Connexion proprietaire</h1>
                    <p class="text-muted mb-4">Accede a la supervision globale de Tacha.</p>
                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control auth-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control auth-input" required>
                        </div>
                        <button class="btn btn-tacha-primary w-100">Se connecter</button>
                    </form>
                    <hr>
                    <small class="text-muted">Compte seed: admin@tacha.cm / 123456</small>
                </section>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

