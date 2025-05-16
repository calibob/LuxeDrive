<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

// Récupérer l'ID de la réservation
$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;

// Vérifier que la réservation existe et appartient à l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT r.*, v.brand, v.model 
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement annulé - Luxury Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height: 60px; margin-bottom: 20px;">
                        </div>
                        <h1 class="card-title mb-4">Paiement annulé</h1>
                        <p class="lead mb-4">
                            Votre paiement pour la réservation de la <?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?> 
                            n'a pas été finalisé.
                        </p>
                        <p>
                            Votre réservation est toujours en attente de paiement. Vous pouvez réessayer de payer 
                            ou annuler votre réservation depuis votre espace client.
                        </p>
                        <div class="mt-4">
                            <a href="payment.php?reservation_id=<?= $reservation_id ?>" class="btn btn-primary btn-lg me-2">
                                Réessayer le paiement
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                Retour à mon compte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
