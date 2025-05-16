<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est admin
redirectIfNotAdmin();

$success = '';
$error = '';

// Gérer le changement de statut
if (isset($_POST['update_status'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    $new_status = sanitizeInput($_POST['status']);
    
    if (in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $reservation_id])) {
            $success = 'Statut mis à jour avec succès';
        } else {
            $error = 'Erreur lors de la mise à jour du statut';
        }
    }
}

// Filtres
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$date_filter = isset($_GET['date_filter']) ? sanitizeInput($_GET['date_filter']) : '';

// Construction de la requête
$sql = "
    SELECT r.*, 
           u.name as user_name, u.email as user_email,
           v.brand, v.model, v.image
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN vehicles v ON r.vehicle_id = v.id
    WHERE 1=1
";
$params = [];

if ($status_filter) {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $sql .= " AND DATE(r.start_date) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND r.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $sql .= " AND r.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - LuxeDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(120deg, #f8f6f2 0%, #f3e9dc 100%); font-family: 'Montserrat', Arial, sans-serif; }
        .sidebar { background: #fff; border-right: 1px solid #eee; min-height: 100vh; }
        .sidebar .nav-link { color: #6c757d; font-weight: 500; border-radius: 0 2rem 2rem 0; margin-bottom: 0.5rem; transition: background 0.2s, color 0.2s; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: linear-gradient(90deg, #bfa046 0%, #fff 100%); color: #bfa046 !important; }
        .dashboard-header { background: rgba(255,255,255,0.85); backdrop-filter: blur(6px); border-radius: 1rem; box-shadow: 0 2px 12px rgba(191,160,70,0.07); position: sticky; top: 0; z-index: 10; }
        .dashboard-header h2 { font-family: 'Playfair Display', serif; color: #bfa046; font-weight: 700; }
        .table-card { background: rgba(255,255,255,0.9); border-radius: 1.25rem; box-shadow: 0 4px 18px rgba(191,160,70,0.08); }
        .table thead th { color: #bfa046; font-weight: 700; font-size: 1rem; background: transparent; border-bottom: 2px solid #f3e9dc; }
        .table tbody tr { transition: background 0.2s; }
        .table tbody tr:hover { background: #fffbe8; }
        .table tbody td { color: #444; font-size: 1rem; }
        .badge.bg-warning { background: linear-gradient(90deg,#ffe066,#ffd700); color: #7c5a00; }
        .badge.bg-success { background: linear-gradient(90deg,#b6e7a6,#60c96b); color: #185b2c; }
        .badge.bg-danger { background: linear-gradient(90deg,#ffb5b5,#ff5252); color: #7c2222; }
        @media (max-width: 767px) { .dashboard-header { padding: 1rem 0.5rem; } }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <!-- Header sticky premium -->
    <div class="dashboard-header p-4 mb-4 d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="mb-0">Gestion des Réservations</h2>
            <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height:50px;">
        </div>
    </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>
                                        En attente
                                    </option>
                                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>
                                        Confirmée
                                    </option>
                                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>
                                        Annulée
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date_filter" class="form-label">Période</label>
                                <select class="form-select" id="date_filter" name="date_filter">
                                    <option value="">Toutes les dates</option>
                                    <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>
                                        Aujourd'hui
                                    </option>
                                    <option value="week" <?= $date_filter === 'week' ? 'selected' : '' ?>>
                                        7 prochains jours
                                    </option>
                                    <option value="month" <?= $date_filter === 'month' ? 'selected' : '' ?>>
                                        30 prochains jours
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des réservations -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Réf.</th>
                                <th>Client</th>
                                <th>Véhicule</th>
                                <th>Dates</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td>#<?= $reservation['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($reservation['user_name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($reservation['user_email']) ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/vehicles/<?= htmlspecialchars($reservation['image']) ?>" 
                                                 alt="<?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>"
                                                 class="img-thumbnail me-2" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>
                                            </div>
                                        </div>
                                    </td>
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
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <li>
                                                        <form method="POST" action="" class="dropdown-item">
                                                            <input type="hidden" name="reservation_id" 
                                                                   value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="status" value="confirmed">
                                                            <button type="submit" name="update_status" 
                                                                    class="btn btn-link text-success p-0">
                                                                <i class="bi bi-check-circle"></i> Confirmer
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="" class="dropdown-item">
                                                            <input type="hidden" name="reservation_id" 
                                                                   value="<?= $reservation['id'] ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" name="update_status" 
                                                                    class="btn btn-link text-danger p-0"
                                                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                                <i class="bi bi-x-circle"></i> Annuler
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a href="#" class="dropdown-item" 
                                                       onclick="showReservationDetails(<?= htmlspecialchars(json_encode($reservation)) ?>)">
                                                        <i class="bi bi-eye"></i> Détails
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="reservationDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la Réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reservationDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showReservationDetails(reservation) {
        const modal = new bootstrap.Modal(document.getElementById('reservationDetailsModal'));
        const detailsDiv = document.getElementById('reservationDetails');
        
        const startDate = new Date(reservation.start_date).toLocaleDateString('fr-FR');
        const endDate = new Date(reservation.end_date).toLocaleDateString('fr-FR');
        const createdAt = new Date(reservation.created_at).toLocaleString('fr-FR');
        
        detailsDiv.innerHTML = `
            <dl class="row">
                <dt class="col-sm-4">Référence</dt>
                <dd class="col-sm-8">#${reservation.id}</dd>
                
                <dt class="col-sm-4">Client</dt>
                <dd class="col-sm-8">${reservation.user_name}<br>
                    <small class="text-muted">${reservation.user_email}</small></dd>
                
                <dt class="col-sm-4">Véhicule</dt>
                <dd class="col-sm-8">${reservation.brand} ${reservation.model}</dd>
                
                <dt class="col-sm-4">Période</dt>
                <dd class="col-sm-8">Du ${startDate}<br>Au ${endDate}</dd>
                
                <dt class="col-sm-4">Prix total</dt>
                <dd class="col-sm-8">${reservation.total_price} €</dd>
                
                <dt class="col-sm-4">Créée le</dt>
                <dd class="col-sm-8">${createdAt}</dd>
            </dl>
        `;
        
        modal.show();
    }
    </script>
</body>
</html>
