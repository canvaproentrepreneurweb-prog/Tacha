<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$baseUrl = '../';
$assetPrefix = '../';
$activeAdminMenu = 'events';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Session expiree, rechargez la page.');
        header('Location: events.php');
        exit;
    }

    if (($_POST['action'] ?? '') === 'toggle_event') {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $newState = (int) ($_POST['new_state'] ?? 1);
        if ($eventId > 0) {
            $up = db()->prepare('UPDATE events SET is_active = ? WHERE id = ?');
            $up->execute([$newState ? 1 : 0, $eventId]);
            set_flash('success', $newState ? 'Evenement reactive.' : 'Evenement suspendu.');
        }
    }

    header('Location: events.php');
    exit;
}

$flash = get_flash();

$stmt = db()->query(
    'SELECT e.id, e.title, e.city, e.venue, e.event_date, e.price, e.is_active,
            u.name AS organizer_name,
            COUNT(t.id) AS sold,
            COALESCE(SUM(CASE WHEN tr.status = "success" THEN tr.amount END), 0) AS revenue
     FROM events e
     JOIN users u ON u.id = e.organizer_id
     LEFT JOIN tickets t ON t.event_id = e.id
     LEFT JOIN transactions tr ON tr.ticket_id = t.id
     GROUP BY e.id
     ORDER BY e.created_at DESC'
);
$events = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Evenements</title>
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
            <h1 class="h3 mb-3">Tous les evenements</h1>
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <div class="table-responsive card form-card p-3">
                <table class="table align-middle mb-0">
                    <thead><tr><th>ID</th><th>Titre</th><th>Ville</th><th>Date</th><th>Organisateur</th><th>Statut</th><th>Prix</th><th>Tickets</th><th>Revenus</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $e): ?>
                        <tr>
                            <td><?= (int) $e['id'] ?></td>
                            <td><?= e($e['title']) ?></td>
                            <td><?= e($e['city']) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($e['event_date']))) ?></td>
                            <td><?= e($e['organizer_name']) ?></td>
                            <td><span class="badge text-bg-<?= (int) $e['is_active'] === 1 ? 'success' : 'danger' ?>"><?= (int) $e['is_active'] === 1 ? 'actif' : 'suspendu' ?></span></td>
                            <td><?= e(format_fcfa((int) $e['price'])) ?></td>
                            <td><?= (int) $e['sold'] ?></td>
                            <td><?= e(format_fcfa((int) $e['revenue'])) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="../event.php?id=<?= (int) $e['id'] ?>&preview=1" class="btn btn-sm btn-outline-primary">Voir</a>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="toggle_event">
                                        <input type="hidden" name="event_id" value="<?= (int) $e['id'] ?>">
                                        <input type="hidden" name="new_state" value="<?= (int) $e['is_active'] === 1 ? 0 : 1 ?>">
                                        <button class="btn btn-sm <?= (int) $e['is_active'] === 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>"><?= (int) $e['is_active'] === 1 ? 'Suspendre' : 'Reactiver' ?></button>
                                    </form>
                                </div>
                            </td>
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

