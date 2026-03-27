<?php
$activeAdminMenu = $activeAdminMenu ?? '';
?>
<div class="org-sidebar">
    <h2 class="h6 fw-bold mb-3">Espace Proprietaire</h2>
    <div class="list-group">
        <a class="list-group-item list-group-item-action <?= $activeAdminMenu === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Tableau de bord</a>
        <a class="list-group-item list-group-item-action <?= $activeAdminMenu === 'users' ? 'active' : '' ?>" href="users.php">Utilisateurs</a>
        <a class="list-group-item list-group-item-action <?= $activeAdminMenu === 'events' ? 'active' : '' ?>" href="events.php">Evenements</a>
        <a class="list-group-item list-group-item-action <?= $activeAdminMenu === 'tickets' ? 'active' : '' ?>" href="tickets.php">Tickets / ventes</a>
    </div>
</div>

