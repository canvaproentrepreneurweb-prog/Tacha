<?php
require_once __DIR__ . '/../config/auth.php';
require_role('organizer');

$baseUrl = '../';
$assetPrefix = '../';
$user = current_user();
$activeOrgMenu = 'dashboard';

$statsStmt = db()->prepare(
    'SELECT
        COUNT(DISTINCT e.id) AS events_count,
        COUNT(DISTINCT t.id) AS tickets_count,
        SUM(CASE WHEN t.status = "used" THEN 1 ELSE 0 END) AS used_count,
        SUM(CASE WHEN tr.status = "success" THEN tr.amount ELSE 0 END) AS revenue
     FROM events e
     LEFT JOIN tickets t ON t.event_id = e.id
     LEFT JOIN transactions tr ON tr.ticket_id = t.id
     WHERE e.organizer_id = ?'
);
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

$listStmt = db()->prepare('SELECT id, title, city, event_date, event_time, price, capacity FROM events WHERE organizer_id = ? ORDER BY event_date ASC');
$listStmt->execute([$user['id']]);
$events = $listStmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Organisateur - Tacha</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold mb-0">Tableau de bord</h1>
                <a class="btn btn-tacha-primary" href="scan.php">Scanner un ticket</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3"><div class="card kpi-card p-3"><small>Evenements</small><div class="kpi-value"><?= (int) ($stats['events_count'] ?? 0) ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card kpi-card p-3"><small>Tickets vendus</small><div class="kpi-value"><?= (int) ($stats['tickets_count'] ?? 0) ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card kpi-card p-3"><small>Entrees validees</small><div class="kpi-value"><?= (int) ($stats['used_count'] ?? 0) ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-3"><div class="card kpi-card p-3"><small>Revenus</small><div class="kpi-value"><?= e(format_fcfa((int) ($stats['revenue'] ?? 0))) ?></div></div></div>
            </div>

            <div class="card form-card p-3">
                <h2 class="h5 mb-3">Mes evenements</h2>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr><th>Titre</th><th>Ville</th><th>Date</th><th>Prix</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$events): ?>
                                <tr><td colspan="5">Aucun evenement.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= e($event['title']) ?></td>
                                    <td><?= e($event['city']) ?></td>
                                    <td><?= e(date('d/m/Y', strtotime($event['event_date']))) ?> <?= e(substr($event['event_time'], 0, 5)) ?></td>
                                    <td><?= e(format_fcfa((int) $event['price'])) ?></td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="../event.php?id=<?= (int) $event['id'] ?>">Voir</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

