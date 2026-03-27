<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$baseUrl = '../';
$assetPrefix = '../';
$activeAdminMenu = 'dashboard';

$kpi = db()->query(
    'SELECT
        (SELECT COUNT(*) FROM users) AS users_count,
        (SELECT COUNT(*) FROM users WHERE role = "organizer") AS organizers_count,
        (SELECT COUNT(*) FROM events) AS events_count,
        (SELECT COUNT(*) FROM tickets) AS tickets_count,
        (SELECT COUNT(*) FROM ticket_validations) AS validations_count,
        (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status = "success") AS revenue_total'
)->fetch();

$recentTx = db()->query(
    'SELECT tr.created_at, tr.amount, tr.method, tr.status, t.token, e.title
     FROM transactions tr
     JOIN tickets t ON t.id = tr.ticket_id
     JOIN events e ON e.id = t.event_id
     ORDER BY tr.created_at DESC
     LIMIT 10'
)->fetchAll();

$topOrganizers = db()->query(
    'SELECT u.name, u.email,
            COUNT(DISTINCT e.id) AS events_count,
            COUNT(DISTINCT t.id) AS tickets_count,
            COALESCE(SUM(CASE WHEN tr.status = "success" THEN tr.amount END), 0) AS revenue
     FROM users u
     LEFT JOIN events e ON e.organizer_id = u.id
     LEFT JOIN tickets t ON t.event_id = e.id
     LEFT JOIN transactions tr ON tr.ticket_id = t.id
     WHERE u.role = "organizer"
     GROUP BY u.id
     ORDER BY revenue DESC
     LIMIT 8'
)->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container org-shell">
        <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>

        <section class="org-main">
            <h1 class="h3 fw-bold mb-4">Vue globale plateforme</h1>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Utilisateurs</small><div class="kpi-value"><?= (int) $kpi['users_count'] ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Organisateurs</small><div class="kpi-value"><?= (int) $kpi['organizers_count'] ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Evenements</small><div class="kpi-value"><?= (int) $kpi['events_count'] ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Tickets emis</small><div class="kpi-value"><?= (int) $kpi['tickets_count'] ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Entrees validees</small><div class="kpi-value"><?= (int) $kpi['validations_count'] ?></div></div></div>
                <div class="col-12 col-sm-6 col-xl-4"><div class="card kpi-card p-3"><small>Revenus globaux</small><div class="kpi-value"><?= e(format_fcfa((int) $kpi['revenue_total'])) ?></div></div></div>
            </div>

            <div class="card form-card p-3 mb-4">
                <h2 class="h5 mb-3">Dernieres transactions</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Date</th><th>Token</th><th>Evenement</th><th>Methode</th><th>Montant</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentTx as $tr): ?>
                            <tr>
                                <td><?= e(date('d/m/Y H:i', strtotime($tr['created_at']))) ?></td>
                                <td><?= e($tr['token']) ?></td>
                                <td><?= e($tr['title']) ?></td>
                                <td><?= e(strtoupper($tr['method'])) ?></td>
                                <td><?= e(format_fcfa((int) $tr['amount'])) ?></td>
                                <td><?= e($tr['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card form-card p-3">
                <h2 class="h5 mb-3">Performance organisateurs</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Organisateur</th><th>Email</th><th>Events</th><th>Tickets</th><th>Revenus</th></tr></thead>
                        <tbody>
                        <?php foreach ($topOrganizers as $o): ?>
                            <tr>
                                <td><?= e($o['name']) ?></td>
                                <td><?= e((string) $o['email']) ?></td>
                                <td><?= (int) $o['events_count'] ?></td>
                                <td><?= (int) $o['tickets_count'] ?></td>
                                <td><?= e(format_fcfa((int) $o['revenue'])) ?></td>
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

