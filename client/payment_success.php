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
$reservation = null;

// Récupérer les paramètres
$session_id = isset($_GET['session_id']) ? sanitizeInput($_GET['session_id']) : '';
$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;

// Vérifier la session Stripe et mettre à jour le statut
if ($session_id && $reservation_id) {
    $checkout_result = checkStripeSession($session_id);
    
    if (isset($checkout_result['error'])) {
        $error = 'Erreur lors de la vérification du paiement: ' . $checkout_result['error'];
    } elseif ($checkout_result['success']) {
        $success = 'Votre paiement a été traité avec succès !';
        
        // Récupérer les détails de la réservation pour l'affichage
        $stmt = $pdo->prepare("
            SELECT r.*, v.brand, v.model, v.image
            FROM reservations r
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.id = ? AND r.user_id = ?
        ");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch();
    } else {
        $error = 'Votre paiement n\'a pas été finalisé. Veuillez réessayer.';
    }
} else {
    $error = 'Informations de paiement manquantes.';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement réussi - Luxury Car Rental</title>
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
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <p>
                                <a href="dashboard.php" class="btn btn-primary">Retour à mon compte</a>
                            </p>
                        <?php elseif ($success && $reservation): ?>
                            <div class="mb-4 text-center">
                                <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height: 60px; margin-bottom: 20px;">
                                <div>
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                                </div>
                            </div>
                            <h1 class="card-title mb-4">Paiement confirmé !</h1>
                            <p class="lead mb-4">
                                Votre réservation pour la <?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?> 
                                a été confirmée et payée avec succès.
                            </p>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5>Récapitulatif de votre réservation #<?= $reservation_id ?></h5>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-md-start">Véhicule :</div>
                                        <div class="col-md-6 text-md-end fw-bold"><?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-md-start">Dates :</div>
                                        <div class="col-md-6 text-md-end fw-bold">
                                            Du <?= formatDate($reservation['start_date']) ?> au <?= formatDate($reservation['end_date']) ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 text-md-start">Montant total :</div>
                                        <div class="col-md-6 text-md-end fw-bold"><?= number_format($reservation['total_price'], 2, ',', ' ') ?> €</div>
                                    </div>
                                </div>
                            </div>
                            <p>
                                Une confirmation a été envoyée à votre adresse email. <br>
                                Vous pouvez consulter vos réservations dans votre espace client.
                            </p>
                            <div class="mt-4">
                                <a href="dashboard.php" class="btn btn-primary btn-lg">Voir mes réservations</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">Informations de réservation manquantes.</div>
                            <p>
                                <a href="dashboard.php" class="btn btn-primary">Retour à mon compte</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css"></script>
</body>
</html>
