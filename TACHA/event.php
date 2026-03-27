<?php
require_once __DIR__ . '/config/auth.php';
$baseUrl = '';
$assetPrefix = '';

$eventId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT e.*, u.name as organizer_name FROM events e JOIN users u ON e.organizer_id = u.id WHERE e.id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    echo 'Evenement introuvable.';
    exit;
}
if ((int) ($event['is_active'] ?? 1) !== 1) {
    $user = current_user();
    $adminPreview = isset($_GET['preview']) && (int) $_GET['preview'] === 1;
    if (!$user || $user['role'] !== 'admin' || !$adminPreview) {
        http_response_code(403);
        echo 'Evenement temporairement suspendu.';
        exit;
    }
}
if ((int) ($event['is_active'] ?? 1) !== 1 && !isset($_GET['preview'])) {
    http_response_code(403);
    echo 'Evenement temporairement suspendu.';
    exit;
}

$salesStmt = db()->prepare(
    'SELECT COALESCE(SUM(t.quantity), 0) AS purchased
     FROM tickets t
     JOIN transactions tr ON tr.ticket_id = t.id
     WHERE t.event_id = ? AND tr.status = "success"'
);
$salesStmt->execute([$event['id']]);
$purchased = (int) ($salesStmt->fetch()['purchased'] ?? 0);
$remaining = max(0, (int) $event['capacity'] - $purchased);
$soldPct = ((int) $event['capacity'] > 0) ? min(100, (int) round(($purchased / (int) $event['capacity']) * 100)) : 0;

$eventTs = strtotime($event['event_date'] . ' ' . $event['event_time']);
$promoHorizonTs = time() + (72 * 3600); // offre marketing: 72h max pour garder un effet urgent
$saleEndTs = $eventTs ? min($eventTs, $promoHorizonTs) : $promoHorizonTs;
$salesEnded = ($saleEndTs !== false) ? ($saleEndTs <= time()) : false;
$originalPrice = (int) round(((int) $event['price']) * 1.35);

$buyLink = 'buy.php?eid=' . (int) $event['id'];
$shareUrl = app_base_url() . '/' . $buyLink;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($event['title']) ?> - Tacha</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section pb-5">
    <div class="container mb-4">
        <div class="offer-top-alert">
            <span class="offer-top-alert-icon">⚠</span>
            Derniere chance pour profiter de la reduction :
            <span class="offer-top-alert-highlight">Offre disponible jusqu'a la fin du chrono</span>
        </div>
    </div>

    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <h1 class="h2 fw-bold mb-3"><?= e($event['title']) ?></h1>
                <div class="offer-meta d-flex flex-wrap gap-3 mb-3">
                    <span><i class="bi bi-geo-alt"></i> <?= e($event['venue']) ?>, <?= e($event['city']) ?></span>
                    <span><i class="bi bi-calendar-event"></i> <?= e(date('d/m/Y', strtotime($event['event_date']))) ?> a <?= e(substr($event['event_time'], 0, 5)) ?></span>
                    <span><i class="bi bi-person-badge"></i> <?= e($event['organizer_name']) ?></span>
                </div>
                <img src="<?= e($event['image_path']) ?>" alt="<?= e($event['title']) ?>" class="img-fluid rounded-4 shadow-sm">

                <div class="mt-4 card form-card p-4">
                    <h2 class="h4">Description</h2>
                    <p class="mb-0"><?= nl2br(e($event['description'])) ?></p>
                </div>
            </div>

            <div class="col-xl-4">
                <aside class="offer-buy-card card form-card p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Vendu: <strong><?= (int) $purchased ?></strong></span>
                        <span>Restant: <strong><?= (int) $remaining ?></strong></span>
                    </div>
                    <div class="progress offer-progress mb-2" role="progressbar" aria-valuenow="<?= (int) $soldPct ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?= (int) $soldPct ?>%"></div>
                    </div>
                    <p class="offer-limited mb-3">Offre a duree limitee</p>

                    <p class="mb-1 offer-price-row">
                        <span class="offer-old-price"><?= e(format_fcfa($originalPrice)) ?></span>
                        <span class="price-tag h3 ms-2 mb-0 align-middle offer-current-price"><?= e(format_fcfa((int) $event['price'])) ?></span>
                    </p>
                    <p class="text-muted mb-3">Capacite totale: <?= (int) $event['capacity'] ?> places</p>

                    <div class="event-countdown mb-3" data-sale-end="<?= (int) $saleEndTs ?>">
                        <span class="countdown-label">L'offre se termine dans</span>
                        <div class="countdown-grid" id="saleCountdownGrid">
                            <div class="countdown-box"><strong id="cdDays">00</strong><small>Jours</small></div>
                            <div class="countdown-box"><strong id="cdHours">00</strong><small>Heures</small></div>
                            <div class="countdown-box"><strong id="cdMins">00</strong><small>Minutes</small></div>
                            <div class="countdown-box"><strong id="cdSecs">00</strong><small>Secondes</small></div>
                        </div>
                        <strong id="saleCountdownText" class="d-block mt-2"><?= $salesEnded ? 'Ventes terminees' : 'Calcul...' ?></strong>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= e($buyLink) ?>" class="btn btn-tacha-green buy-btn-animated <?= $salesEnded ? 'disabled' : '' ?>" <?= $salesEnded ? 'aria-disabled="true" tabindex="-1"' : '' ?>>Acheter maintenant</a>
                        <button class="btn btn-tacha-primary" type="button" onclick="navigator.clipboard.writeText('<?= e($shareUrl) ?>'); this.innerText='Lien copie';">Partager le lien d'achat</button>
                    </div>
                    <small class="d-block mt-2 text-muted"><?= e($shareUrl) ?></small>
                </aside>
            </div>
        </div>        
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const container = document.querySelector('.event-countdown');
        const target = document.getElementById('saleCountdownText');
        if (!container || !target) return;

        const saleEnd = Number(container.getAttribute('data-sale-end') || 0) * 1000;
        if (!saleEnd) {
            target.textContent = 'Date non definie';
            return;
        }
        const dEl = document.getElementById('cdDays');
        const hEl = document.getElementById('cdHours');
        const mEl = document.getElementById('cdMins');
        const sEl = document.getElementById('cdSecs');

        function render() {
            const diff = saleEnd - Date.now();
            if (diff <= 0) {
                target.textContent = 'Ventes terminees';
                if (dEl) dEl.textContent = '00';
                if (hEl) hEl.textContent = '00';
                if (mEl) mEl.textContent = '00';
                if (sEl) sEl.textContent = '00';
                return;
            }
            const totalSec = Math.floor(diff / 1000);
            const days = Math.floor(totalSec / 86400);
            const hours = Math.floor((totalSec % 86400) / 3600);
            const mins = Math.floor((totalSec % 3600) / 60);
            const secs = totalSec % 60;
            target.textContent = `${days}j ${hours}h ${mins}m ${secs}s`;
            if (dEl) dEl.textContent = String(days).padStart(2, '0');
            if (hEl) hEl.textContent = String(hours).padStart(2, '0');
            if (mEl) mEl.textContent = String(mins).padStart(2, '0');
            if (sEl) sEl.textContent = String(secs).padStart(2, '0');
            requestAnimationFrame(() => setTimeout(render, 1000));
        }

        render();
    })();
</script>
</body>
</html>

