<?php
require_once __DIR__ . '/config/auth.php';
require_login();

$baseUrl = '';
$assetPrefix = '';
$user = current_user();
$flash = get_flash();

$stmt = db()->prepare(
    'SELECT t.id, t.token, t.status, t.quantity, t.created_at, e.title, e.city, e.event_date, e.event_time
     FROM tickets t
     JOIN events e ON e.id = t.event_id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC'
);
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mes tickets - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container">
        <h1 class="h2 fw-bold mb-4">Mes tickets</h1>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="table-responsive card form-card p-3">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Evenement</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$tickets): ?>
                    <tr><td colspan="5">Aucun ticket pour le moment.</td></tr>
                <?php endif; ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><strong><?= e($ticket['token']) ?></strong></td>
                        <td><?= e($ticket['title']) ?> (<?= e($ticket['city']) ?>)</td>
                        <td><?= e(date('d/m/Y', strtotime($ticket['event_date']))) ?> <?= e(substr($ticket['event_time'], 0, 5)) ?></td>
                        <td>
                            <span class="badge text-bg-<?= $ticket['status'] === 'used' ? 'secondary' : ($ticket['status'] === 'revoked' ? 'danger' : 'success') ?>">
                                <?= $ticket['status'] === 'used' ? 'Utilise' : ($ticket['status'] === 'revoked' ? 'Desactive' : 'Valide') ?>
                            </span>
                        </td>
                        <td><a href="ticket.php?ticket_id=<?= (int) $ticket['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

