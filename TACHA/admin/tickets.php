<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$baseUrl = '../';
$assetPrefix = '../';
$activeAdminMenu = 'tickets';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Session expiree, rechargez la page.');
        header('Location: tickets.php');
        exit;
    }

    if (($_POST['action'] ?? '') === 'set_ticket_status') {
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? 'valid';
        if ($ticketId > 0 && in_array($newStatus, ['valid', 'revoked'], true)) {
            $up = db()->prepare('UPDATE tickets SET status = ? WHERE id = ?');
            $up->execute([$newStatus, $ticketId]);
            set_flash('success', $newStatus === 'revoked' ? 'Ticket desactive.' : 'Ticket reactive.');
        }
    }

    header('Location: tickets.php');
    exit;
}

$flash = get_flash();

$status = trim($_GET['status'] ?? '');
$where = '';
$params = [];
if (in_array($status, ['valid', 'used', 'revoked'], true)) {
    $where = 'WHERE t.status = ?';
    $params[] = $status;
}

$stmt = db()->prepare(
    "SELECT t.id, t.token, t.status, t.quantity, t.created_at, t.buyer_firstname, t.buyer_lastname, t.buyer_email, t.buyer_phone,
            e.title, e.city,
            org.name AS organizer_name,
            tr.amount, tr.method, tr.status AS tx_status,
            u.name AS participant
     FROM tickets t
     JOIN events e ON e.id = t.event_id
     JOIN users org ON org.id = e.organizer_id
     LEFT JOIN users u ON u.id = t.user_id
     LEFT JOIN transactions tr ON tr.ticket_id = t.id
     $where
     ORDER BY t.created_at DESC"
);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Tickets</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Tickets / ventes</h1>
                <form method="get" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        <option value="valid" <?= $status === 'valid' ? 'selected' : '' ?>>Valide</option>
                        <option value="used" <?= $status === 'used' ? 'selected' : '' ?>>Utilise</option>
                        <option value="revoked" <?= $status === 'revoked' ? 'selected' : '' ?>>Desactive</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Filtrer</button>
                </form>
            </div>

            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

            <div class="table-responsive card form-card p-3">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Token</th><th>Evenement</th><th>Acheteur</th><th>Organisateur</th><th>Montant</th><th>Paiement</th><th>Etat ticket</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <?php $buyer = ticket_holder_name($t); ?>
                        <tr>
                            <td><?= e($t['token']) ?></td>
                            <td><?= e($t['title']) ?> (<?= e($t['city']) ?>)</td>
                            <td><?= e($buyer) ?></td>
                            <td><?= e($t['organizer_name']) ?></td>
                            <td><?= e(format_fcfa((int) ($t['amount'] ?? 0))) ?></td>
                            <td><?= e(strtoupper((string) ($t['method'] ?? '-'))) ?> / <?= e((string) ($t['tx_status'] ?? '-')) ?></td>
                            <td><span class="badge text-bg-<?= $t['status'] === 'used' ? 'secondary' : ($t['status'] === 'revoked' ? 'danger' : 'success') ?>"><?= e($t['status']) ?></span></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($t['created_at']))) ?></td>
                            <td>
                                <?php if ($t['status'] !== 'used'): ?>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="set_ticket_status">
                                    <input type="hidden" name="ticket_id" value="<?= (int) $t['id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $t['status'] === 'revoked' ? 'valid' : 'revoked' ?>">
                                    <button class="btn btn-sm <?= $t['status'] === 'revoked' ? 'btn-outline-success' : 'btn-outline-danger' ?>"><?= $t['status'] === 'revoked' ? 'Reactiver' : 'Desactiver' ?></button>
                                </form>
                                <?php else: ?>
                                    <small class="text-muted">Utilise</small>
                                <?php endif; ?>
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

