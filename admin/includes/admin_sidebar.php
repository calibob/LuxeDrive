<?php
// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar-premium {
        background: #fff;
        border-right: 1px solid #eee;
        min-height: 100vh;
        border-radius: 1.5rem 0 0 1.5rem;
        box-shadow: 2px 0 16px rgba(191,160,70,0.07);
        padding-top: 1.5rem;
    }
    .sidebar-premium .nav-link {
        color: #6c757d;
        font-weight: 500;
        border-radius: 0 2rem 2rem 0;
        margin-bottom: 0.5rem;
        transition: background 0.2s, color 0.2s;
        display: flex;
        align-items: center;
        font-size: 1.05rem;
        padding: 0.75rem 1.25rem;
    }
    .sidebar-premium .nav-link.active, .sidebar-premium .nav-link:hover {
        background: linear-gradient(90deg, #bfa046 0%, #fff 100%);
        color: #bfa046 !important;
    }
    .sidebar-premium .nav-link i {
        margin-right: 0.75rem;
        font-size: 1.3em;
    }
    .sidebar-premium .sidebar-heading {
        color: #bfa046;
        font-weight: 700;
        letter-spacing: 0.03em;
        font-size: 1rem;
        margin-top: 2rem;
    }
</style>
<nav class="col-md-3 col-lg-2 d-md-block sidebar-premium sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?= $current_page === 'dashboard.php' ? ' active' : '' ?>" href="dashboard.php" aria-current="<?= $current_page === 'dashboard.php' ? 'page' : false ?>">
                    <i class="bi bi-speedometer2"></i>
                    Tableau de Bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $current_page === 'vehicles.php' ? ' active' : '' ?>" href="vehicles.php" aria-current="<?= $current_page === 'vehicles.php' ? 'page' : false ?>">
                    <i class="bi bi-car-front"></i>
                    Véhicules
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $current_page === 'reservations.php' ? ' active' : '' ?>" href="reservations.php" aria-current="<?= $current_page === 'reservations.php' ? 'page' : false ?>">
                    <i class="bi bi-calendar-check"></i>
                    Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $current_page === 'users.php' ? ' active' : '' ?>" href="users.php" aria-current="<?= $current_page === 'users.php' ? 'page' : false ?>">
                    <i class="bi bi-people"></i>
                    Utilisateurs
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
        <!-- Statistiques rapides -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1">
            <span>Statistiques rapides</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <?php
            // Récupérer quelques statistiques rapides
            $stats = [
                'reservations_pending' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn(),
                'vehicles_available' => $pdo->query("SELECT COUNT(*) FROM vehicles WHERE available = 1")->fetchColumn()
            ];
            ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="../reservations.php?status=pending">
                    <i class="bi bi-clock-history"></i>
                    <?= $stats['reservations_pending'] ?> réservation(s) en attente
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="../vehicles.php">
                    <i class="bi bi-car-front"></i>
                    <?= $stats['vehicles_available'] ?> véhicule(s) disponible(s)
                </a>
            </li>
        </ul>
    </div>
</nav>
