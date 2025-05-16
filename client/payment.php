<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/payment_functions.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

$error = '';
$payment_url = '';

// Récupérer les informations de la réservation
$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;

// Vérifier que la réservation existe et appartient à l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT r.*, v.brand, v.model, v.image, v.price_per_day, u.email
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ? AND r.user_id = ? AND r.payment_status = 'unpaid'
");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header('Location: dashboard.php');
    exit();
}

// Générer la session de paiement simulée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkout = createStripeCheckoutSession(
        $reservation_id, 
        $reservation['total_price'], 
        $reservation['email']
    );
    
    if (isset($checkout['error'])) {
        $error = 'Erreur lors de la création de la session de paiement: ' . $checkout['error'];
    } else {
        // Rediriger vers la page de simulation de paiement
        if (strpos($checkout['url'], 'payment_simulation.php') === 0) {
            header('Location: ' . $checkout['url']);
        } else {
            header('Location: payment_simulation.php?session_id=' . $checkout['id'] . '&reservation_id=' . $reservation_id . '&amount=' . $reservation['total_price'] . '&email=' . urlencode($reservation['email']));
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Réservation #<?= $reservation_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
        <h1 class="mb-4">Paiement de votre réservation</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Détails de la réservation</h5>
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
                        <div class="d-flex justify-content-between">
                            <h5>Montant total:</h5>
                            <h5><?= number_format($reservation['total_price'], 2, ',', ' ') ?> €</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Choisir un mode de paiement</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" checked>
                                    <label class="form-check-label" for="stripe">
                                        <img src="../assets/images/payment/stripe.svg" alt="Stripe" style="height: 25px;"> Payer par carte bancaire
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Procéder au paiement (<?= number_format($reservation['total_price'], 2, ',', ' ') ?> €)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
