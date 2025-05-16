<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/payment_functions.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

$error = '';
$success = '';
$reservation_id = 0;

// Récupérer les informations du véhicule
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND available = 1");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: ../catalog.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = sanitizeInput($_POST['start_date']);
    $end_date = sanitizeInput($_POST['end_date']);
    
    // Validation des dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime();
    
    if ($start < $today) {
        $error = 'La date de début doit être ultérieure à aujourd\'hui';
    } elseif ($end <= $start) {
        $error = 'La date de fin doit être ultérieure à la date de début';
    } else {
        // Vérifier si le véhicule est disponible pour ces dates
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE vehicle_id = ? 
            AND status != 'cancelled'
            AND (
                (start_date BETWEEN ? AND ?) OR
                (end_date BETWEEN ? AND ?) OR
                (start_date <= ? AND end_date >= ?)
            )
        ");
        $stmt->execute([
            $vehicle_id, 
            $start_date, $end_date,
            $start_date, $end_date,
            $start_date, $end_date
        ]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'Le véhicule n\'est pas disponible pour ces dates';
        } else {
            // Calculer le prix total
            $interval = $start->diff($end);
            $days = $interval->days + 1; // +1 car on compte le jour de début
            $total_price = $vehicle['price_per_day'] * $days;
            
            // Créer la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, total_price)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $_SESSION['user_id'],
                $vehicle_id,
                $start_date,
                $end_date,
                $total_price
            ])) {
                // Récupérer l'ID de la réservation créée
                $reservation_id = $pdo->lastInsertId();
                
                // Rediriger vers la page de paiement
                header('Location: payment.php?reservation_id=' . $reservation_id);
                exit();
            } else {
                $error = 'Une erreur est survenue lors de la réservation';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
        <div class="row">
            <!-- Informations du véhicule -->
            <div class="col-md-6">
                <div class="card">
                    <img src="../assets/images/vehicles/<?= htmlspecialchars($vehicle['image']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>
                        </h5>
                        <p class="card-text">
                            <strong>Année:</strong> <?= htmlspecialchars($vehicle['year']) ?><br>
                            <strong>Prix par jour:</strong> <?= htmlspecialchars($vehicle['price_per_day']) ?>€
                        </p>
                        <p class="card-text"><?= htmlspecialchars($vehicle['description']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Formulaire de réservation -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Réservation</h5>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                                <br>
                                <a href="/client/dashboard.php" class="alert-link">
                                    Voir mes réservations
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="" id="reservationForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Date de début</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Date de fin</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Prix total estimé</label>
                                    <div class="form-control" id="total_price">0 €</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Confirmer la réservation
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pricePerDay = <?= $vehicle['price_per_day'] ?>;
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const totalPriceDiv = document.getElementById('total_price');

        // Configuration du calendrier
        const config = {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                calculateTotal();
            }
        };

        flatpickr(startDateInput, config);
        flatpickr(endDateInput, config);

        function calculateTotal() {
            if (startDateInput.value && endDateInput.value) {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                
                if (end >= start) {
                    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    const total = days * pricePerDay;
                    totalPriceDiv.textContent = total.toFixed(2) + ' €';
                } else {
                    totalPriceDiv.textContent = 'Date de fin invalide';
                }
            }
        }

        // Validation du formulaire
        const form = document.getElementById('reservationForm');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    </script>
</body>
</html>
