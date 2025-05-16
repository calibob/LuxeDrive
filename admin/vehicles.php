<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est admin
redirectIfNotAdmin();

$success = '';
$error = '';

// Supprimer un véhicule
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = (int)$_POST['vehicle_id'];
    
    // Vérifier s'il y a des réservations actives
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations 
        WHERE vehicle_id = ? AND status != 'cancelled'
    ");
    $stmt->execute([$vehicle_id]);
    
    if ($stmt->fetchColumn() > 0) {
        $error = 'Impossible de supprimer ce véhicule : il a des réservations actives';
    } else {
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        if ($stmt->execute([$vehicle_id])) {
            $success = 'Véhicule supprimé avec succès';
        } else {
            $error = 'Erreur lors de la suppression du véhicule';
        }
    }
}

// Ajouter/Modifier un véhicule
if (isset($_POST['save_vehicle'])) {
    $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
    $brand = sanitizeInput($_POST['brand']);
    $model = sanitizeInput($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price_per_day'];
    $description = sanitizeInput($_POST['description']);
    
    // Gestion de l'image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            $upload_dir = '../assets/images/vehicles/';
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $newname)) {
                $image = $newname;
            }
        }
    }
    
    if ($vehicle_id) {
        // Modification
        $sql = "UPDATE vehicles SET brand = ?, model = ?, year = ?, price_per_day = ?, description = ?";
        $params = [$brand, $model, $year, $price, $description];
        
        if ($image) {
            $sql .= ", image = ?";
            $params[] = $image;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $vehicle_id;
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $success = 'Véhicule modifié avec succès';
        } else {
            $error = 'Erreur lors de la modification du véhicule';
        }
    } else {
        // Ajout
        if (!$image) {
            $error = 'L\'image est obligatoire pour un nouveau véhicule';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO vehicles (brand, model, year, price_per_day, description, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$brand, $model, $year, $price, $description, $image])) {
                $success = 'Véhicule ajouté avec succès';
            } else {
                $error = 'Erreur lors de l\'ajout du véhicule';
            }
        }
    }
}

// Récupérer tous les véhicules
$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY brand, model")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - LuxeDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <h2 class="mb-0">Gestion des Véhicules</h2>
            <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height:50px;">
        </div>
    </div>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Véhicules</h1>
                    <button type="button" 
                            class="btn btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#vehicleModal">
                        <i class="bi bi-plus-circle"></i> Ajouter un véhicule
                    </button>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Liste des véhicules -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Marque</th>
                                <th>Modèle</th>
                                <th>Année</th>
                                <th>Prix/jour</th>
                                <th>Disponible</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/images/vehicles/<?= htmlspecialchars($vehicle['image']) ?>" 
                                             alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>"
                                             class="img-thumbnail" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?= htmlspecialchars($vehicle['brand']) ?></td>
                                    <td><?= htmlspecialchars($vehicle['model']) ?></td>
                                    <td><?= htmlspecialchars($vehicle['year']) ?></td>
                                    <td><?= number_format($vehicle['price_per_day'], 2) ?> €</td>
                                    <td>
                                        <span class="badge bg-<?= $vehicle['available'] ? 'success' : 'danger' ?>">
                                            <?= $vehicle['available'] ? 'Oui' : 'Non' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary edit-vehicle" 
                                                data-vehicle='<?= json_encode($vehicle) ?>'
                                                data-bs-toggle="modal" 
                                                data-bs-target="#vehicleModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')">
                                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                            <button type="submit" name="delete_vehicle" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Ajout/Modification véhicule -->
    <div class="modal fade" id="vehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicleModalLabel">Ajouter un véhicule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="vehicle_id" id="vehicle_id">
                        
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marque</label>
                            <input type="text" class="form-control" id="brand" name="brand" required>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Modèle</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>

                        <div class="mb-3">
                            <label for="year" class="form-label">Année</label>
                            <input type="number" class="form-control" id="year" name="year" 
                                   min="1900" max="<?= date('Y') + 1 ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_day" class="form-label">Prix par jour (€)</label>
                            <input type="number" class="form-control" id="price_per_day" name="price_per_day" 
                                   min="0" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div id="imageHelp" class="form-text">
                                Format acceptés : JPG, JPEG, PNG, GIF
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="save_vehicle" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du modal pour l'édition
        const vehicleModal = document.getElementById('vehicleModal');
        const modalTitle = vehicleModal.querySelector('.modal-title');
        const vehicleForm = vehicleModal.querySelector('form');
        const vehicleId = vehicleForm.querySelector('#vehicle_id');
        const brand = vehicleForm.querySelector('#brand');
        const model = vehicleForm.querySelector('#model');
        const year = vehicleForm.querySelector('#year');
        const price = vehicleForm.querySelector('#price_per_day');
        const description = vehicleForm.querySelector('#description');
        
        // Réinitialiser le formulaire à l'ouverture du modal
        vehicleModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            if (button.classList.contains('edit-vehicle')) {
                const vehicle = JSON.parse(button.dataset.vehicle);
                modalTitle.textContent = 'Modifier le véhicule';
                vehicleId.value = vehicle.id;
                brand.value = vehicle.brand;
                model.value = vehicle.model;
                year.value = vehicle.year;
                price.value = vehicle.price_per_day;
                description.value = vehicle.description;
            } else {
                modalTitle.textContent = 'Ajouter un véhicule';
                vehicleForm.reset();
                vehicleId.value = '';
            }
        });
    });
    </script>
</body>
</html>
