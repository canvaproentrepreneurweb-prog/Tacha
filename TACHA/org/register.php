<?php
require_once __DIR__ . '/../config/auth.php';

$baseUrl = '../';
$assetPrefix = '../';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $termsAccepted = isset($_POST['terms']);

    $shopName = trim($_POST['shop_name'] ?? '');
    $phone = '';
    $shopCity = null;
    $shopPhone = null;

    if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '' || $shopName === '') {
        $error = 'Tous les champs obligatoires doivent etre remplis.';
    } elseif (!$termsAccepted) {
        $error = 'Veuillez accepter les termes et conditions.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'La confirmation du mot de passe ne correspond pas.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, phone, email, password_hash, role, shop_name, shop_city, shop_phone) VALUES (?, ?, ?, ?, "organizer", ?, ?, ?)');
            $stmt->execute([$name, $phone, $email, password_hash($password, PASSWORD_DEFAULT), $shopName, $shopCity ?: null, $shopPhone ?: null]);
            login_user($email, $password);
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Impossible de creer ce compte (email deja utilise ?).';
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inscription Organisateur - Tacha</title>
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
                    <h2 class="auth-showcase-title">Creez votre boutique et commencez a vendre vos billets.</h2>
                    <div class="auth-tags">
                        <span class="auth-tag"><i class="bi bi-bag-check"></i> Boutique</span>
                        <span class="auth-tag"><i class="bi bi-calendar-plus"></i> Creation Event</span>
                        <span class="auth-tag"><i class="bi bi-graph-up"></i> Revenus</span>
                        <span class="auth-tag"><i class="bi bi-check2-square"></i> Controle entree</span>
                    </div>
                </section>
            </div>

            <div class="col-lg-6 p-3 p-lg-4">
                <section class="auth-form-wrap">
                    <h1 class="auth-title mb-2">Creer une boutique organisateur</h1>
                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                    <form method="post" class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" class="form-control auth-input" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Nom de la boutique</label>
                            <input type="text" name="shop_name" class="form-control auth-input" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control auth-input" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control auth-input" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Confirmation du mot de passe</label>
                            <input type="password" name="password_confirm" class="form-control auth-input" required>
                        </div>
                        <div class="col-12 form-check mt-1">
                            <input class="form-check-input" type="checkbox" value="1" id="termsRegister" name="terms" required>
                            <label class="form-check-label" for="termsRegister">J'accepte les termes et conditions</label>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-tacha-green">Creer mon compte</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

