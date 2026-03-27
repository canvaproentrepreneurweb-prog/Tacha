<?php
require_once __DIR__ . '/../config/auth.php';
require_role('organizer');

$baseUrl = '../';
$assetPrefix = '../';
$user = current_user();
$activeOrgMenu = 'events';
$error = '';
$success = '';
$templatePreview = '../' . image_path('Baniere_accuil.png');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $eventTime = trim($_POST['event_time'] ?? '');
    $price = (int) ($_POST['price'] ?? 0);
    $capacity = (int) ($_POST['capacity'] ?? 0);

    $imagePath = image_path('Baniere_accuil.png');
    $ticketTemplatePath = image_path('Baniere_accuil.png');

    if ($title === '' || $city === '' || $venue === '' || $eventDate === '' || $eventTime === '' || $price <= 0 || $capacity <= 0) {
        $error = 'Remplissez tous les champs obligatoires.';
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!empty($_FILES['event_image']['name']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $name = 'img/uploads/event_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $target = __DIR__ . '/../' . $name;
                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target)) {
                    $imagePath = $name;
                }
            }
        }

        if (!empty($_FILES['ticket_template']['name']) && $_FILES['ticket_template']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['ticket_template']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $name = 'img/uploads/ticket_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $target = __DIR__ . '/../' . $name;
                if (move_uploaded_file($_FILES['ticket_template']['tmp_name'], $target)) {
                    $ticketTemplatePath = $name;
                    $templatePreview = '../' . $name;
                }
            }
        }

        if ($ticketTemplatePath === image_path('Baniere_accuil.png')) {
            $error = 'Le template ticket est obligatoire (image).';
        } else {
            $stmt = db()->prepare(
                'INSERT INTO events (title, city, venue, event_date, event_time, price, capacity, image_path, organizer_id, description, ticket_template_path, qr_x, qr_y, qr_size)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 220)'
            );
            $stmt->execute([$title, $city, $venue, $eventDate, $eventTime, $price, $capacity, $imagePath, $user['id'], $description, $ticketTemplatePath]);
            $success = 'Evenement cree avec succes.';
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Creer un evenement - Tacha</title>
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
            <h1 class="h3 mb-3">Creer un evenement</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6"><label class="form-label">Titre</label><input type="text" name="title" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Ville</label><input type="text" name="city" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Lieu</label><input type="text" name="venue" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="event_date" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Heure</label><input type="time" name="event_time" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Prix (FCFA)</label><input type="number" name="price" class="form-control" min="1" required></div>
                <div class="col-md-3"><label class="form-label">Capacite</label><input type="number" name="capacity" class="form-control" min="1" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                <div class="col-md-6"><label class="form-label">Image evenement (optionnel)</label><input type="file" name="event_image" class="form-control" accept="image/*"></div>
                <div class="col-md-6"><label class="form-label">Ticket template (obligatoire)</label><input type="file" name="ticket_template" class="form-control" accept="image/*" required></div>
                <div class="col-12"><button class="btn btn-tacha-green">Enregistrer</button></div>
            </form>

            <hr class="my-4">
            <h2 class="h5 mb-3">Ticket type (preview)</h2>
            <div class="ticket-preview">
                <img src="<?= e($templatePreview) ?>" alt="Template ticket">
                <div class="qr-placeholder">QR ici</div>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/TACHA/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>

