<?php
require_once __DIR__ . '/../config/auth.php';
require_role('organizer');

$baseUrl = '../';
$assetPrefix = '../';
$activeOrgMenu = 'scan';
$flash = get_flash();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Scan manuel - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container org-shell">
        <?php include __DIR__ . '/../components/org_sidebar.php'; ?>

        <section class="org-main">
            <h1 class="h3 mb-3">Validation ticket (manuel)</h1>
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <form method="post" action="validate.php">
                <div class="mb-3">
                    <label class="form-label">Token ticket</label>
                    <input type="text" name="token" class="form-control" placeholder="TCH-2026-XXXXXX" required>
                </div>
                <button class="btn btn-tacha-green">Valider</button>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

