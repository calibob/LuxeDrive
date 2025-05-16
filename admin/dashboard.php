<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est admin
redirectIfNotAdmin();

// Récupérer les statistiques
$stats = [
    'vehicles' => $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn(),
    'reservations' => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn()
];

// Récupérer les dernières réservations
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, v.brand, v.model 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN vehicles v ON r.vehicle_id = v.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recent_reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - LuxeDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f8f6f2 0%, #f3e9dc 100%);
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .sidebar {
            background: #fff;
            border-right: 1px solid #eee;
            min-height: 100vh;
        }
        .sidebar .nav-link {
            color: #6c757d;
            font-weight: 500;
            border-radius: 0 2rem 2rem 0;
            margin-bottom: 0.5rem;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: linear-gradient(90deg, #bfa046 0%, #fff 100%);
            color: #bfa046 !important;
        }
        .dashboard-header {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(6px);
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(191,160,70,0.07);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .dashboard-header h2 {
            font-family: 'Playfair Display', serif;
            color: #bfa046;
            font-weight: 700;
        }
        .stat-card {
            background: rgba(255,255,255,0.75);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 6px 24px rgba(191,160,70,0.08);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-4px) scale(1.025);
            box-shadow: 0 12px 32px rgba(191,160,70,0.13);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: #bfa046;
            filter: drop-shadow(0 2px 8px #bfa04633);
        }
        .stat-title {
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #bfa046;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #222;
        }
        .card.table-card {
            background: rgba(255,255,255,0.9);
            border-radius: 1.25rem;
            box-shadow: 0 4px 18px rgba(191,160,70,0.08);
        }
        .table thead th {
            color: #bfa046;
            font-weight: 700;
            font-size: 1rem;
            background: transparent;
            border-bottom: 2px solid #f3e9dc;
        }
        .table tbody tr {
            transition: background 0.2s;
        }
        .table tbody tr:hover {
            background: #fffbe8;
        }
        .table tbody td {
            color: #444;
            font-size: 1rem;
        }
        .badge.bg-warning { background: linear-gradient(90deg,#ffe066,#ffd700); color: #7c5a00; }
        .badge.bg-success { background: linear-gradient(90deg,#b6e7a6,#60c96b); color: #185b2c; }
        .badge.bg-danger { background: linear-gradient(90deg,#ffb5b5,#ff5252); color: #7c2222; }
        @media (max-width: 767px) {
            .stat-card { margin-bottom: 1rem; }
            .dashboard-header { padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="vehicles.php">
                                <i class="bi bi-car-front"></i> Véhicules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reservations.php">
                                <i class="bi bi-calendar-check"></i> Réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="users.php">
                                <i class="bi bi-people"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <!-- Header sticky premium -->
    <div class="dashboard-header p-4 mb-4 d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center">
            <h2 class="mb-0">Tableau de bord</h2>
        </div>
        <div class="d-flex align-items-center">
            <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height: 50px;">
        </div>
        <div>
            <span class="text-muted" style="font-size:1.1rem;">Bienvenue, <span style="color:#bfa046; font-weight:700;">
                <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?> (Admin)</span></span>
        </div>
    </div>

    <!-- Alertes dynamiques (succès/erreur) -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <!-- Statistiques glassmorphism -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-title mb-1">Véhicules</div>
                        <div class="stat-value"><?= $stats['vehicles'] ?></div>
                    </div>
                    <i class="bi bi-car-front stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-title mb-1">Clients</div>
                        <div class="stat-value"><?= $stats['users'] ?></div>
                    </div>
                    <i class="bi bi-people stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-title mb-1">Réservations</div>
                        <div class="stat-value"><?= $stats['reservations'] ?></div>
                    </div>
                    <i class="bi bi-calendar-check stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-title mb-1">En attente</div>
                        <div class="stat-value"><?= $stats['pending'] ?></div>
                    </div>
                    <i class="bi bi-clock-history stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières réservations look premium -->
    <div class="card table-card shadow mb-4">
        <div class="card-header py-3 bg-transparent border-0">
            <h6 class="m-0 fw-bold" style="color:#bfa046;">Dernières Réservations</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Véhicule</th>
                            <th>Dates</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reservations as $r): ?>
                            <tr>
                                <td><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($r['user_name']) ?></td>
                                <td><i class="bi bi-car-front me-1"></i> <?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></td>
                                <td>
                                    <i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y', strtotime($r['start_date'])) ?> - <?= date('d/m/Y', strtotime($r['end_date'])) ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'cancelled' => 'danger'
                                    ][$r['status']];
                                    $statusText = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'cancelled' => 'Annulée'
                                    ][$r['status']];
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> px-3 py-2" style="font-size:1rem;font-weight:600;">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

                <!-- Dernières réservations -->
                <div class="card table-card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center">
                        <h6 class="m-0 fw-bold">Dernières Réservations</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Véhicule</th>
                                        <th>Dates</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reservations as $reservation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['user_name']) ?></td>
                                            <td><?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?></td>
                                            <td>
                                                Du <?= date('d/m/Y', strtotime($reservation['start_date'])) ?><br>
                                                Au <?= date('d/m/Y', strtotime($reservation['end_date'])) ?>
                                            </td>
                                            <td><?= number_format($reservation['total_price'], 2) ?> €</td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'cancelled' => 'danger'
                                                ][$reservation['status']];
                                                
                                                $statusText = [
                                                    'pending' => 'En attente',
                                                    'confirmed' => 'Confirmée',
                                                    'cancelled' => 'Annulée'
                                                ][$reservation['status']];
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
