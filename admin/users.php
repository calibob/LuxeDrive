<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est admin
redirectIfNotAdmin();

$success = '';
$error = '';

// Désactiver/Réactiver un utilisateur
if (isset($_POST['toggle_user'])) {
    $user_id = (int)$_POST['user_id'];
    $active = (int)$_POST['active'];
    
    // Ne pas permettre de désactiver l'admin principal
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] === 'admin') {
        $error = 'Impossible de désactiver un compte administrateur';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET active = ? WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$active, $user_id])) {
            $success = 'Statut de l\'utilisateur mis à jour avec succès';
        } else {
            $error = 'Erreur lors de la mise à jour du statut';
        }
    }
}

// Filtres
$role_filter = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Construction de la requête
$sql = "
    SELECT u.*,
           COUNT(r.id) as total_reservations,
           SUM(CASE WHEN r.status = 'confirmed' THEN r.total_price ELSE 0 END) as total_spent
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    WHERE 1=1
";
$params = [];

if ($role_filter) {
    $sql .= " AND u.role = ?";
    $params[] = $role_filter;
}

if ($status_filter !== '') {
    $sql .= " AND u.active = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - LuxeDrive</title>
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
            <h2 class="mb-0">Gestion des Utilisateurs</h2>
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
                            <div class="col-md-3">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Nom ou email">
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="client" <?= $role_filter === 'client' ? 'selected' : '' ?>>
                                        Client
                                    </option>
                                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>
                                        Administrateur
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>
                                        Actif
                                    </option>
                                    <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>
                                        Inactif
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Réservations</th>
                                <th>Total dépensé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                            <?= $user['role'] === 'admin' ? 'Administrateur' : 'Client' ?>
                                        </span>
                                    </td>
                                    <td><?= $user['total_reservations'] ?></td>
                                    <td><?= number_format($user['total_spent'], 2) ?> €</td>
                                    <td>
                                        <span class="badge bg-<?= ($user['role'] === 'admin' || ($user['active'] ?? 0)) ? 'success' : 'secondary' ?>">
                                            <?= ($user['role'] === 'admin' || ($user['active'] ?? 0)) ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="active" value="<?= ($user['active'] ?? 0) ? '0' : '1' ?>">
                                                <button type="submit" 
                                                        name="toggle_user" 
                                                        class="btn btn-sm btn-<?= ($user['active'] ?? 0) ? 'warning' : 'success' ?>"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir <?= ($user['active'] ?? 0) ? 'désactiver' : 'réactiver' ?> cet utilisateur ?')">
                                                    <?= ($user['active'] ?? 0) ? 'Désactiver' : 'Réactiver' ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-info"
                                                onclick="showUserDetails(<?= htmlspecialchars(json_encode($user)) ?>)">
                                            Détails
                                        </button>
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
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showUserDetails(user) {
        const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
        const detailsDiv = document.getElementById('userDetails');
        
        const createdAt = new Date(user.created_at).toLocaleString('fr-FR');
        
        detailsDiv.innerHTML = `
            <dl class="row">
                <dt class="col-sm-4">Nom</dt>
                <dd class="col-sm-8">${user.name}</dd>
                
                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">${user.email}</dd>
                
                <dt class="col-sm-4">Rôle</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'}">
                        ${user.role === 'admin' ? 'Administrateur' : 'Client'}
                    </span>
                </dd>
                
                <dt class="col-sm-4">Statut</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-${user.active ? 'success' : 'secondary'}">
                        ${user.active ? 'Actif' : 'Inactif'}
                    </span>
                </dd>
                
                <dt class="col-sm-4">Réservations</dt>
                <dd class="col-sm-8">${user.total_reservations}</dd>
                
                <dt class="col-sm-4">Total dépensé</dt>
                <dd class="col-sm-8">${parseFloat(user.total_spent).toFixed(2)} €</dd>
                
                <dt class="col-sm-4">Inscrit le</dt>
                <dd class="col-sm-8">${createdAt}</dd>
            </dl>
        `;
        
        modal.show();
    }
    </script>
</body>
</html>
