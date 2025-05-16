<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Récupération des filtres
$brand = isset($_GET['brand']) ? sanitizeInput($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_FLOAT_MAX;
$available = isset($_GET['available']) ? (bool)$_GET['available'] : true;

// Pagination
$per_page = 9;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Construction de la requête SQL de base
$sql = "SELECT * FROM vehicles WHERE 1=1";
$params = [];

if ($brand) {
    $sql .= " AND brand = ?";
    $params[] = $brand;
}

if ($min_price) {
    $sql .= " AND price_per_day >= ?";
    $params[] = $min_price;
}

if ($max_price < PHP_FLOAT_MAX) {
    $sql .= " AND price_per_day <= ?";
    $params[] = $max_price;
}

if ($available) {
    $sql .= " AND available = 1";
}

// Pour pagination : compter le total
$sql_count = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_vehicles = (int)$stmt_count->fetchColumn();
$total_pages = (int)ceil($total_vehicles / $per_page);

$sql .= " ORDER BY price_per_day ASC LIMIT $per_page OFFSET $offset";

// Exécution de la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

// Récupération des marques distinctes pour le filtre
$brands = $pdo->query("SELECT DISTINCT brand FROM vehicles ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Luxury Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Alertes dynamiques -->
    <div class="container mt-4">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>


    <main class="container my-5" id="catalog-list">
        <div class="row">
            <!-- Filtres -->
            <div class="col-md-3">
                <div class="filter-form">
                    <h5 class="mb-4">Filtres</h5>
                    <form id="filterForm" method="GET" action="">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marque</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">Toutes les marques</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= htmlspecialchars($b) ?>" 
                                            <?= $b === $brand ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="price-range-container">
                            <label class="form-label">Prix par jour (€)</label>
                            <div id="price-range"></div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="min_price" name="min_price" 
                                           value="<?= $min_price ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" 
                                           id="max_price" name="max_price" 
                                           value="<?= $max_price < PHP_FLOAT_MAX ? $max_price : '' ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="available" name="available" value="1" 
                                       <?= $available ? 'checked' : '' ?>>
                                <label class="form-check-label" for="available">
                                    Disponible uniquement
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Appliquer</button>
                    </form>
                </div>
            </div>

            <!-- Liste des véhicules -->
            <div class="col-md-9">
                <div class="row">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 position-relative">
                                <img src="<?= htmlspecialchars($vehicle['image']) ? 'assets/images/vehicles/' . htmlspecialchars($vehicle['image']) : 'https://images.unsplash.com/photo-1511918984145-48de785d4c4e?auto=format&fit=crop&w=800&q=80' ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>">
                                <span class="price-badge position-absolute top-0 start-0 m-3" style="background:var(--secondary-color);font-size:1rem;"><?= htmlspecialchars($vehicle['price_per_day']) ?>€/jour</span>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></h5>
                                    <p class="card-text mb-2">
                                        <i class="bi bi-calendar"></i> <?= htmlspecialchars($vehicle['year']) ?> &nbsp;|
                                        <i class="bi bi-people"></i> <?= htmlspecialchars($vehicle['seats'] ?? '4') ?> places
                                    </p>
                                    <p class="card-text small mb-2 text-muted">
                                        <?= htmlspecialchars($vehicle['description']) ?>
                                    </p>
                                    <?php if ($vehicle['available']): ?>
                                        <a href="/luxury-car-rental/client/reservation.php?vehicle_id=<?= $vehicle['id'] ?>" class="btn btn-primary w-100">Réserver</a>                                    <?php else: ?>
                                        <span class="badge bg-secondary">Indisponible</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($vehicles)): ?>
                        <div class="col-12 no-results">
                            <div class="alert alert-info">
                                Aucun véhicule ne correspond à vos critères de recherche.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Pagination des véhicules">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Précédent">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item<?= $i == $page ? ' active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"> <?= $i ?> </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item<?= $page >= $total_pages ? ' disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Suivant">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.7.0/nouislider.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation du slider de prix
        const priceRange = document.getElementById('price-range');
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');

        noUiSlider.create(priceRange, {
            start: [
                <?= $min_price ?>, 
                <?= $max_price < PHP_FLOAT_MAX ? $max_price : 3000 ?>
            ],
            connect: true,
            range: {
                'min': 0,
                'max': 3000
            },
            step: 100
        });

        // Mise à jour des inputs lors du déplacement du slider
        priceRange.noUiSlider.on('update', function(values, handle) {
            const value = Math.round(values[handle]);
            if (handle === 0) {
                minPriceInput.value = value;
            } else {
                maxPriceInput.value = value;
            }
        });

        // Mise à jour du slider lors de la modification des inputs
        minPriceInput.addEventListener('change', function() {
            priceRange.noUiSlider.set([this.value, null]);
        });

        maxPriceInput.addEventListener('change', function() {
            priceRange.noUiSlider.set([null, this.value]);
        });
    });
    </script>
</body>
</html>
