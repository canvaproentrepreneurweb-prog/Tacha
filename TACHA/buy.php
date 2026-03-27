<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/lib/phpqrcode/qrlib.php';

$baseUrl = '';
$assetPrefix = '';

$eventId = (int) ($_GET['eid'] ?? $_POST['eid'] ?? 0);
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

$errors = [];
$successData = null;
$phase = $_POST['phase'] ?? 'form';

$form = [
    'firstname' => trim($_POST['firstname'] ?? ''),
    'lastname' => trim($_POST['lastname'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'quantity' => (int) ($_POST['quantity'] ?? 1),
    'method' => $_POST['method'] ?? 'simu',
];

if ($phase === 'finish') {
    $quantity = max(1, min(10, $form['quantity']));
    $method = in_array($form['method'], ['mtn', 'orange', 'simu'], true) ? $form['method'] : 'simu';

    if ($form['firstname'] === '' || $form['lastname'] === '') {
        $errors[] = 'Nom et prenom requis.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }
    if ($form['phone'] === '') {
        $errors[] = 'Telephone requis.';
    }

    if (!$errors) {
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
            $ticketStmt = db()->prepare('INSERT INTO tickets (event_id, user_id, quantity, token, status, buyer_firstname, buyer_lastname, buyer_email, buyer_phone) VALUES (?, NULL, ?, ?, "valid", ?, ?, ?, ?)');
            $ticketStmt->execute([
                $event['id'],
                $quantity,
                $token,
                $form['firstname'],
                $form['lastname'],
                $form['email'],
                $form['phone'],
            ]);
            $ticketId = (int) db()->lastInsertId();

            $amount = (int) $lockedEvent['price'] * $quantity;
            $txStmt = db()->prepare('INSERT INTO transactions (ticket_id, method, amount, status) VALUES (?, ?, ?, "success")');
            $txStmt->execute([$ticketId, $method, $amount]);

            db()->commit();

            $qrFile = __DIR__ . '/generated/qr/' . $token . '.png';
            if (!is_file($qrFile)) {
                QRcode::png($token, $qrFile, 'L', 6, 2);
            }

            $successData = [
                'ticket_id' => $ticketId,
                'token' => $token,
                'amount' => $amount,
                'qr_web' => 'generated/qr/' . $token . '.png',
            ];
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $errors[] = ($e instanceof RuntimeException) ? $e->getMessage() : 'Paiement indisponible, reessayez.';
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Achat billet - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container" style="max-width:820px;">
        <div class="card form-card p-4 p-md-5">
            <h1 class="h3 mb-3">Achat billet public</h1>
            <p class="mb-1"><strong><?= e($event['title']) ?></strong></p>
            <p class="mb-1"><?= e($event['city']) ?> - <?= e($event['venue']) ?></p>
            <p class="mb-3"><?= e(date('d/m/Y', strtotime($event['event_date']))) ?> - <?= e(substr($event['event_time'], 0, 5)) ?> | <strong><?= e(format_fcfa((int) $event['price'])) ?></strong></p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endforeach; ?>

            <?php if ($phase === 'start'): ?>
                <div class="buy-step mb-3">
                    <h2 class="h5 mb-2">Validation paiement Mobile Money</h2>
                    <p class="mb-1">Consultez votre telephone et entrez votre code secret.</p>
                    <small class="text-muted">Simulation en cours... validation automatique.</small>
                </div>
                <form id="autoFinishForm" method="post">
                    <?php foreach ($form as $k => $v): ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e((string) $v) ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="eid" value="<?= (int) $event['id'] ?>">
                    <input type="hidden" name="phase" value="finish">
                </form>
                <script>
                    setTimeout(function () {
                        document.getElementById('autoFinishForm').submit();
                    }, 2200);
                </script>
            <?php elseif ($successData): ?>
                <div class="alert alert-success">Paiement reussi. Votre ticket est valide.</div>
                <p class="mb-2">Token: <span class="ticket-token"><?= e($successData['token']) ?></span></p>
                <p class="mb-3">Montant debite: <strong><?= e(format_fcfa((int) $successData['amount'])) ?></strong></p>
                <img src="<?= e($successData['qr_web']) ?>" alt="QR ticket" class="border rounded p-2 bg-white" style="width:220px;max-width:100%;">
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-tacha-primary" href="ticket.php?t=<?= urlencode($successData['token']) ?>">Voir mon ticket</a>
                    <a class="btn btn-tacha-green" href="ticket_pdf.php?t=<?= urlencode($successData['token']) ?>" target="_blank">Telecharger mon ticket (PDF)</a>
                </div>
            <?php else: ?>
                <form method="post" class="row g-3">
                    <input type="hidden" name="eid" value="<?= (int) $event['id'] ?>">
                    <input type="hidden" name="phase" value="start">

                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="lastname" required value="<?= e($form['lastname']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prenom</label>
                        <input type="text" class="form-control" name="firstname" required value="<?= e($form['firstname']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required value="<?= e($form['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telephone (numero a debiter)</label>
                        <input type="text" class="form-control" name="phone" required value="<?= e($form['phone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantite</label>
                        <input type="number" class="form-control" name="quantity" min="1" max="10" value="<?= max(1, (int) $form['quantity']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Paiement</label>
                        <select name="method" class="form-select">
                            <option value="mtn" <?= $form['method'] === 'mtn' ? 'selected' : '' ?>>MTN Mobile Money</option>
                            <option value="orange" <?= $form['method'] === 'orange' ? 'selected' : '' ?>>Orange Money</option>
                            <option value="simu" <?= $form['method'] === 'simu' ? 'selected' : '' ?>>Simulation locale</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-tacha-green">Acheter maintenant</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

