<?php
$activeOrgMenu = $activeOrgMenu ?? '';
?>
<div class="org-sidebar">
    <h2 class="h6 fw-bold mb-3">Espace Organisateur</h2>
    <div class="list-group">
        <a class="list-group-item list-group-item-action <?= $activeOrgMenu === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Tableau de bord</a>
        <a class="list-group-item list-group-item-action <?= $activeOrgMenu === 'events' ? 'active' : '' ?>" href="events.php">Mes evenements</a>
        <a class="list-group-item list-group-item-action <?= $activeOrgMenu === 'sales' ? 'active' : '' ?>" href="events.php#sales">Ventes / Tickets</a>
        <a class="list-group-item list-group-item-action <?= $activeOrgMenu === 'scan' ? 'active' : '' ?>" href="scan.php">Scanner</a>
        <a class="list-group-item list-group-item-action <?= $activeOrgMenu === 'settings' ? 'active' : '' ?>" href="register.php">Parametres</a>
    </div>
</div>

