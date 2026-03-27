<?php
require_once __DIR__ . '/../config/auth.php';
require_role('organizer');

$baseUrl = '../';
$assetPrefix = '../';
$user = current_user();
$activeOrgMenu = 'events';

$stmt = db()->prepare(
    'SELECT e.id, e.title, e.city, e.venue, e.event_date, e.event_time, e.price, e.capacity,
            COUNT(t.id) AS sold,
            SUM(CASE WHEN tr.status = "success" THEN tr.amount ELSE 0 END) AS revenue
     FROM events e
     LEFT JOIN tickets t ON t.event_id = e.id
     LEFT JOIN transactions tr ON tr.ticket_id = t.id
     WHERE e.organizer_id = ?
     GROUP BY e.id
     ORDER BY e.created_at DESC'
);
$stmt->execute([$user['id']]);
$events = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mes evenements - Tacha</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Mes evenements</h1>
                <a class="btn btn-tacha-green" href="event-create.php">Nouveau evenement</a>
            </div>

            <div class="table-responsive" id="sales">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Ville</th>
                            <th>Prix</th>
                            <th>Ventes</th>
                            <th>Revenus</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$events): ?>
                            <tr><td colspan="7">Aucun evenement cree.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= e($event['title']) ?></td>
                                <td><?= e(date('d/m/Y', strtotime($event['event_date']))) ?> <?= e(substr($event['event_time'], 0, 5)) ?></td>
                                <td><?= e($event['city']) ?></td>
                                <td><?= e(format_fcfa((int) $event['price'])) ?></td>
                                <td><?= (int) $event['sold'] ?></td>
                                <td><?= e(format_fcfa((int) $event['revenue'])) ?></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="../event.php?id=<?= (int) $event['id'] ?>">Voir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

