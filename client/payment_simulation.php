<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/payment_functions.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

// Récupérer les paramètres
$session_id = isset($_GET['session_id']) ? sanitizeInput($_GET['session_id']) : '';
$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;
$email = isset($_GET['email']) ? sanitizeInput($_GET['email']) : '';

// Vérifier que la session existe et que l'utilisateur est le bon
if (!$session_id || !$reservation_id) {
    header('Location: dashboard.php');
    exit();
}

// Récupérer les informations de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, v.brand, v.model, v.image 
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

// Traitement du formulaire de paiement simulé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simuler un délai de traitement
    sleep(1);
    
    // Rediriger vers la page de succès avec les paramètres nécessaires
    header('Location: payment_success.php?session_id=' . $session_id . '&reservation_id=' . $reservation_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Luxury Car Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .payment-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .card-input {
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #f8f9fa;
            font-size: 16px;
        }
        .card-element {
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #f8f9fa;
            margin-bottom: 20px;
        }
        .card-header-custom {
            background: #1a1a1a;
            color: #bfa046;
            padding: 15px;
            font-weight: bold;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .payment-card {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .brand-logo {
            height: 25px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container my-5">
        <h2 class="text-center mb-4">Finaliser votre paiement</h2>
        
        <div class="row">
            <!-- Détails de la réservation -->
            <div class="col-md-5 mb-4">
                <div class="card h-100 payment-card">
                    <div class="card-header-custom">
                        Récapitulatif de votre réservation
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <img src="../assets/images/vehicles/<?= htmlspecialchars($reservation['image']) ?>" 
                                alt="<?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>" 
                                class="img-thumbnail me-3" style="width: 100px;">
                            <div>
                                <h5><?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?></h5>
                                <p class="mb-0">
                                    <strong>Du:</strong> <?= formatDate($reservation['start_date']) ?><br>
                                    <strong>Au:</strong> <?= formatDate($reservation['end_date']) ?>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Montant total:</span>
                            <span><?= number_format($amount, 2, ',', ' ') ?> €</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de paiement simulé -->
            <div class="col-md-7">
                <div class="card payment-card">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center">
                            <img src="../assets/images/payment/stripe.svg" alt="Stripe" class="brand-logo"> 
                            Paiement sécurisé
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="payment-form" method="POST" class="payment-form needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="card-name" class="form-label">Nom sur la carte</label>
                                <input type="text" id="card-name" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="card-number" class="form-label">Numéro de carte</label>
                                <input type="text" id="card-number" class="form-control" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242" required>
                                <div class="form-text text-muted">Pour tester, utilisez le numéro 4242 4242 4242 4242</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="card-expiry" class="form-label">Date d'expiration</label>
                                    <input type="text" id="card-expiry" class="form-control" placeholder="MM/AA" value="12/25" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="card-cvc" class="form-label">CVC</label>
                                    <input type="text" id="card-cvc" class="form-control" placeholder="123" value="123" required>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <button type="submit" id="submit-button" class="btn btn-primary btn-lg w-100">
                                Payer <?= number_format($amount, 2, ',', ' ') ?> €
                            </button>
                            
                            <div class="mt-3 text-center">
                                <img src="https://www.transparentpng.com/thumb/payment-method/MDCqNh-payment-method-card-transparent.png" 
                                     alt="Modes de paiement acceptés" style="max-width: 250px;">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simuler un chargement
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Traitement en cours...';
                
                // Soumettre le formulaire après un délai pour simuler le traitement
                setTimeout(() => {
                    form.submit();
                }, 1500);
            });
        });
    </script>
</body>
</html>
