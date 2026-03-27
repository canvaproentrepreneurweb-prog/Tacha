<?php
require_once __DIR__ . '/config/auth.php';
$baseUrl = '';
$assetPrefix = '';

$q = trim($_GET['q'] ?? '');
$city = trim($_GET['city'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($q !== '') {
    $where[] = 'title LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($city !== '') {
    $where[] = 'city = ?';
    $params[] = $city;
}
$where[] = 'is_active = 1';

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = db()->prepare("SELECT COUNT(*) as total FROM events $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetch()['total'];
$totalPages = max(1, (int) ceil($total / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$listStmt = db()->prepare("SELECT id, title, city, venue, event_date, event_time, price, image_path FROM events $whereSql ORDER BY event_date ASC LIMIT $limit OFFSET $offset");
$listStmt->execute($params);
$events = $listStmt->fetchAll();

$cityStmt = db()->query('SELECT DISTINCT city FROM events ORDER BY city');
$cities = $cityStmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tacha - Evenements</title>
    <link href="/TACHA/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="/TACHA/vendor/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="content-section">
    <div class="container">
        <h1 class="h2 fw-bold mb-4">Tous les evenements</h1>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Rechercher un evenement" value="<?= e($q) ?>">
            </div>
            <div class="col-md-4">
                <select name="city" class="form-select">
                    <option value="">Toutes les villes</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= e($c['city']) ?>" <?= $city === $c['city'] ? 'selected' : '' ?>><?= e($c['city']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-tacha-primary">Filtrer</button>
            </div>
        </form>

        <div class="row g-4">
            <?php if (!$events): ?>
                <div class="col-12"><div class="alert alert-info">Aucun evenement trouve.</div></div>
            <?php endif; ?>

            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card event-card">
                        <img src="<?= e($event['image_path']) ?>" alt="<?= e($event['title']) ?>" class="card-img-top">
                        <div class="card-body">
                            <span class="badge badge-city mb-2"><?= e($event['city']) ?></span>
                            <h3 class="h5"><?= e($event['title']) ?></h3>
                            <p class="mb-1"><i class="bi bi-calendar-event"></i> <?= e(date('d/m/Y', strtotime($event['event_date']))) ?> - <?= e(substr($event['event_time'], 0, 5)) ?></p>
                            <p class="price-tag mb-3"><?= e(format_fcfa((int) $event['price'])) ?></p>
                            <a class="btn btn-tacha-primary" href="event.php?id=<?= (int) $event['id'] ?>">Voir details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="pagination">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?q=<?= urlencode($q) ?>&city=<?= urlencode($city) ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

