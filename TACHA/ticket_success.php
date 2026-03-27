<?php
require_once __DIR__ . '/config/auth.php';
require_login();

$baseUrl = '';
$assetPrefix = '';
$user = current_user();
$ticketId = (int) ($_GET['ticket_id'] ?? 0);

$stmt = db()->prepare('SELECT t.id, t.token, e.title FROM tickets t JOIN events e ON e.id = t.event_id WHERE t.id = ? AND t.user_id = ? LIMIT 1');
$stmt->execute([$ticketId, $user['id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    set_flash('danger', 'Ticket introuvable.');
    header('Location: my_tickets.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Achat confirme - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container" style="max-width:760px;">
        <div class="card form-card p-4 p-md-5 text-center">
            <h1 class="h2 text-success">Paiement reussi</h1>
            <p class="mb-1">Votre ticket pour <strong><?= e($ticket['title']) ?></strong> est cree.</p>
            <p class="mb-4">Token: <span class="ticket-token"><?= e($ticket['token']) ?></span></p>
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a class="btn btn-tacha-primary" href="ticket.php?ticket_id=<?= (int) $ticket['id'] ?>">Voir ticket</a>
                <a class="btn btn-tacha-green" href="ticket_pdf.php?t=<?= urlencode($ticket['token']) ?>" target="_blank">Telecharger PDF</a>
                <a class="btn btn-outline-primary" href="my_tickets.php">Mes tickets</a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

