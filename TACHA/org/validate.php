<?php
require_once __DIR__ . '/../config/auth.php';
require_role('organizer');

$baseUrl = '../';
$assetPrefix = '../';
$activeOrgMenu = 'scan';
$user = current_user();

$token = trim($_POST['token'] ?? '');
$resultType = 'danger';
$resultMessage = 'Le ticket est introuvable.';
$ticket = null;

if ($token === '') {
    set_flash('danger', 'Token manquant.');
    header('Location: scan.php');
    exit;
}

$stmt = db()->prepare(
    'SELECT t.id, t.token, t.status, t.user_id, t.buyer_firstname, t.buyer_lastname,
            e.id AS event_id, e.title, e.organizer_id,
            u.name AS participant
     FROM tickets t
     JOIN events e ON e.id = t.event_id
     LEFT JOIN users u ON u.id = t.user_id
     WHERE t.token = ?
     LIMIT 1'
);
$stmt->execute([$token]);
$ticket = $stmt->fetch();

if ($ticket) {
    if ((int) $ticket['organizer_id'] !== (int) $user['id']) {
        $resultMessage = 'Pas autorise: ce ticket ne correspond pas a vos evenements.';
    } elseif ($ticket['status'] !== 'valid') {
        $resultMessage = 'Ticket deja utilise.';
    } else {
        db()->beginTransaction();
        try {
            $upd = db()->prepare('UPDATE tickets SET status = "used" WHERE id = ?');
            $upd->execute([$ticket['id']]);

            $ins = db()->prepare('INSERT INTO ticket_validations (ticket_id, organizer_id, validated_at) VALUES (?, ?, NOW())');
            $ins->execute([$ticket['id'], $user['id']]);
            db()->commit();

            $resultType = 'success';
            $resultMessage = 'Ticket valide. Entree autorisee.';
            $ticket['status'] = 'used';
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $resultMessage = 'Erreur technique pendant la validation.';
        }
    }
}

$holder = $ticket ? ticket_holder_name($ticket) : '-';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Resultat validation - Tacha</title>
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
            <div class="card scan-result p-4 border-<?= e($resultType) ?>">
                <h1 class="h4 text-<?= e($resultType) ?>"><?= $resultType === 'success' ? 'Ticket valide' : 'Ticket invalide' ?></h1>
                <p class="mb-4"><?= e($resultMessage) ?></p>

                <?php if ($ticket): ?>
                    <ul class="list-group mb-4">
                        <li class="list-group-item"><strong>Token:</strong> <?= e($ticket['token']) ?></li>
                        <li class="list-group-item"><strong>Evenement:</strong> <?= e($ticket['title']) ?></li>
                        <li class="list-group-item"><strong>Acheteur:</strong> <?= e($holder) ?></li>
                        <li class="list-group-item"><strong>Statut:</strong> <?= e($ticket['status']) ?></li>
                    </ul>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-tacha-primary" href="scan.php">Scanner un autre ticket</a>
                    <a class="btn btn-tacha-green" href="dashboard.php">Retour dashboard</a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

