<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/lib/phpqrcode/qrlib.php';

$baseUrl = '';
$assetPrefix = '';

$ticketId = (int) ($_GET['ticket_id'] ?? 0);
$token = trim($_GET['t'] ?? '');
$user = current_user();

if ($ticketId <= 0 && $token === '') {
    http_response_code(400);
    echo 'Parametre ticket manquant.';
    exit;
}

if ($token !== '') {
    $stmt = db()->prepare(
        'SELECT t.*, e.title, e.city, e.venue, e.event_date, e.event_time, e.organizer_id,
                u.name as holder_name
         FROM tickets t
         JOIN events e ON e.id = t.event_id
         LEFT JOIN users u ON u.id = t.user_id
         WHERE t.token = ?
         LIMIT 1'
    );
    $stmt->execute([$token]);
} else {
    if (!$user) {
        require_login();
    }

    $stmt = db()->prepare(
        'SELECT t.*, e.title, e.city, e.venue, e.event_date, e.event_time, e.organizer_id,
                u.name as holder_name
         FROM tickets t
         JOIN events e ON e.id = t.event_id
         LEFT JOIN users u ON u.id = t.user_id
         WHERE t.id = ?
         LIMIT 1'
    );
    $stmt->execute([$ticketId]);
}
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    echo 'Ticket introuvable.';
    exit;
}

if ($token === '') {
    $canView = ((int) $ticket['user_id'] === (int) $user['id']) || ($user['role'] === 'organizer' && (int) $ticket['organizer_id'] === (int) $user['id']);
    if (!$canView) {
        http_response_code(403);
        echo 'Acces refuse.';
        exit;
    }
}

$holder = ticket_holder_name($ticket);
$qrPath = __DIR__ . '/generated/qr/' . $ticket['token'] . '.png';
if (!is_file($qrPath)) {
    QRcode::png($ticket['token'], $qrPath, 'L', 6, 2);
}
$qrWeb = 'generated/qr/' . $ticket['token'] . '.png';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ticket <?= e($ticket['token']) ?> - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container" style="max-width:900px;">
        <div class="card form-card p-4 p-md-5">
            <div class="row g-4 align-items-center">
                <div class="col-md-7">
                    <h1 class="h3 mb-3">Ticket evenement</h1>
                    <p class="mb-1"><strong>Evenement:</strong> <?= e($ticket['title']) ?></p>
                    <p class="mb-1"><strong>Lieu:</strong> <?= e($ticket['venue']) ?>, <?= e($ticket['city']) ?></p>
                    <p class="mb-1"><strong>Date:</strong> <?= e(date('d/m/Y', strtotime($ticket['event_date']))) ?> <?= e(substr($ticket['event_time'], 0, 5)) ?></p>
                    <p class="mb-1"><strong>Porteur:</strong> <?= e($holder) ?></p>
                    <p class="mb-3"><strong>Statut:</strong>
                        <span class="badge text-bg-<?= $ticket['status'] === 'used' ? 'secondary' : ($ticket['status'] === 'revoked' ? 'danger' : 'success') ?>">
                            <?= $ticket['status'] === 'used' ? 'Utilise' : ($ticket['status'] === 'revoked' ? 'Desactive' : 'Valide') ?>
                        </span>
                    </p>
                    <div class="ticket-token"><?= e($ticket['token']) ?></div>
                    <div class="mt-3">
                        <a class="btn btn-tacha-green" href="ticket_pdf.php?t=<?= urlencode($ticket['token']) ?>" target="_blank">Telecharger mon ticket (PDF)</a>
                    </div>
                </div>
                <div class="col-md-5 text-center">
                    <img src="<?= e($qrWeb) ?>" alt="QR Ticket" class="border rounded-3 bg-white p-2" style="max-width:230px;width:100%;">
                    <div class="mt-2 text-muted small">QR local genere depuis le token</div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

