<?php
require_once __DIR__ . '/config/auth.php';
require_login();

$baseUrl = '';
$assetPrefix = '';
$user = current_user();

$eventId = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    echo 'Evenement introuvable.';
    exit;
}
if ((int) ($event['is_active'] ?? 1) !== 1) {
    http_response_code(403);
    echo 'Cet evenement est actuellement suspendu.';
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, min(10, (int) ($_POST['quantity'] ?? 1)));
    $method = $_POST['method'] ?? 'simu';
    $allowedMethods = ['mtn', 'orange', 'card', 'simu'];
    if (!in_array($method, $allowedMethods, true)) {
        $method = 'simu';
    }

    try {
        db()->beginTransaction();

        $lockEventStmt = db()->prepare('SELECT id, capacity, is_active, price FROM events WHERE id = ? FOR UPDATE');
        $lockEventStmt->execute([$event['id']]);
        $lockedEvent = $lockEventStmt->fetch();
        if (!$lockedEvent || (int) $lockedEvent['is_active'] !== 1) {
            throw new RuntimeException('Evenement indisponible.');
        }

        $soldStmt = db()->prepare('SELECT COALESCE(SUM(quantity), 0) AS sold FROM tickets WHERE event_id = ? FOR UPDATE');
        $soldStmt->execute([$event['id']]);
        $sold = (int) ($soldStmt->fetch()['sold'] ?? 0);
        if (($sold + $quantity) > (int) $lockedEvent['capacity']) {
            throw new RuntimeException('Capacite depassee.');
        }

        $token = generate_ticket_token();
        $ticketStmt = db()->prepare('INSERT INTO tickets (event_id, user_id, quantity, token, status, buyer_firstname, buyer_lastname, buyer_email, buyer_phone) VALUES (?, ?, ?, ?, "valid", ?, ?, ?, ?)');
        $ticketStmt->execute([
            $event['id'],
            $user['id'],
            $quantity,
            $token,
            $user['name'],
            '',
            $user['email'],
            $user['phone'],
        ]);
        $ticketId = (int) db()->lastInsertId();

        $amount = (int) $lockedEvent['price'] * $quantity;
        $txStmt = db()->prepare('INSERT INTO transactions (ticket_id, method, amount, status) VALUES (?, ?, ?, "success")');
        $txStmt->execute([$ticketId, $method, $amount]);

        db()->commit();
        header('Location: ticket_success.php?ticket_id=' . $ticketId);
        exit;
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = ($e instanceof RuntimeException) ? $e->getMessage() : 'Paiement simule indisponible, reessaye.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Checkout - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container" style="max-width:780px;">
        <div class="card form-card p-4 p-md-5">
            <h1 class="h3 mb-3">Paiement simule</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

            <div class="mb-3">
                <h2 class="h5 mb-1"><?= e($event['title']) ?></h2>
                <p class="mb-1"><?= e($event['city']) ?> - <?= e(date('d/m/Y', strtotime($event['event_date']))) ?></p>
                <p class="price-tag mb-0"><?= e(format_fcfa((int) $event['price'])) ?> / billet</p>
            </div>

            <form method="post">
                <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Quantite</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Methode</label>
                    <select name="method" class="form-select">
                        <option value="simu">Simulation locale</option>
                        <option value="mtn">MTN Mobile Money</option>
                        <option value="orange">Orange Money</option>
                        <option value="card">Carte bancaire</option>
                    </select>
                </div>

                <button class="btn btn-tacha-green">Valider le paiement</button>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

