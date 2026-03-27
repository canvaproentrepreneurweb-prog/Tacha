<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');

$baseUrl = '../';
$assetPrefix = '../';
$activeAdminMenu = 'users';
$currentAdmin = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Session expiree, rechargez la page.');
        header('Location: users.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'change_role') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? '';
        $allowed = ['participant', 'organizer', 'admin'];

        if ($targetId > 0 && in_array($newRole, $allowed, true)) {
            if ($targetId === (int) $currentAdmin['id'] && $newRole !== 'admin') {
                set_flash('danger', 'Vous ne pouvez pas retirer votre propre role admin.');
            } else {
                $up = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
                $up->execute([$newRole, $targetId]);
                set_flash('success', 'Role utilisateur mis a jour.');
            }
        }
    }

    header('Location: users.php');
    exit;
}

$flash = get_flash();
$role = trim($_GET['role'] ?? '');
$where = '';
$params = [];
if (in_array($role, ['participant', 'organizer', 'admin'], true)) {
    $where = 'WHERE role = ?';
    $params[] = $role;
}

$stmt = db()->prepare("SELECT id, name, email, phone, role, shop_name, created_at FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Utilisateurs</title>
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
                <h1 class="h3 mb-0">Utilisateurs plateforme</h1>
                <form method="get" class="d-flex gap-2">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Tous roles</option>
                        <option value="participant" <?= $role === 'participant' ? 'selected' : '' ?>>Participant</option>
                        <option value="organizer" <?= $role === 'organizer' ? 'selected' : '' ?>>Organisateur</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Filtrer</button>
                </form>
            </div>

            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

            <div class="table-responsive card form-card p-3">
                <table class="table align-middle mb-0">
                    <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Telephone</th><th>Role</th><th>Boutique</th><th>Inscription</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int) $u['id'] ?></td>
                            <td><?= e($u['name']) ?></td>
                            <td><?= e((string) $u['email']) ?></td>
                            <td><?= e($u['phone']) ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($u['role']) ?></span></td>
                            <td><?= e((string) $u['shop_name']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($u['created_at']))) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <select name="new_role" class="form-select form-select-sm">
                                        <option value="participant" <?= $u['role'] === 'participant' ? 'selected' : '' ?>>participant</option>
                                        <option value="organizer" <?= $u['role'] === 'organizer' ? 'selected' : '' ?>>organizer</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Appliquer</button>
                                </form>
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

