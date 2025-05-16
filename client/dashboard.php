<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/payment_functions.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

// Récupérer les réservations de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.*, v.brand, v.model, v.image, 
           (SELECT COUNT(*) FROM payments p WHERE p.reservation_id = r.id AND p.status = 'completed') as payment_completed
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();

// Gérer l'annulation d'une réservation
if (isset($_POST['cancel_reservation'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET status = 'cancelled' 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    
    if ($stmt->execute([$reservation_id, $_SESSION['user_id']])) {
        header('Location: dashboard.php?success=cancelled');
        exit();
    }
}

// Vérifier si un paiement a été effectué avec succès
if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $success_message = 'Votre paiement a été traité avec succès. Votre réservation est maintenant confirmée.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - Luxury Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
    <!-- Section bienvenue personnalisée -->
    <div class="mb-4 p-4 rounded shadow-sm bg-dark text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1a1a1a 60%, #bfa046 100%);">
        <div>
            <h2 class="mb-1" style="font-family: var(--font-heading); font-weight:700;">Bienvenue, <span style="color:#bfa046;"><?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?></span> !</h2>
            <p class="mb-0">Voici votre espace personnel et vos réservations de véhicules de luxe.</p>
        </div>
        <div>
            <img src="../assets/images/luxedrive-logo.svg" alt="Luxe Drive" style="height: 60px;">
        </div>
    </div>

    <!-- Alertes dynamiques -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            La réservation a été annulée avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Menu latéral modernisé -->
            <div class="list-group shadow-sm" style="border-radius:12px;overflow:hidden;">
                <a href="dashboard.php" class="list-group-item list-group-item-action active d-flex align-items-center">
                    <i class="bi bi-calendar2-check me-2"></i> Mes Réservations
                </a>
                <a href="profile.php" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="bi bi-person me-2"></i> Mon Profil
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <!-- Liste des réservations -->
            <?php if (empty($reservations)): ?>
                <div class="alert alert-info">
                    Vous n'avez pas encore de réservations.
                    <a href="../catalog.php" class="alert-link">Parcourir le catalogue</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($reservations as $reservation): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 bg-dark text-white position-relative">
                                <img src="../assets/images/vehicles/<?= htmlspecialchars($reservation['image']) ?>" alt="<?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>" class="card-img-top" style="height: 170px; object-fit: cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                                <div class="card-body">
                                    <h5 class="card-title mb-2" style="color:#bfa046;">
                                        <?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>
                                    </h5>
                                    <p class="mb-1"><i class="bi bi-calendar-event"></i> Du <strong><?= date('d/m/Y', strtotime($reservation['start_date'])) ?></strong> au <strong><?= date('d/m/Y', strtotime($reservation['end_date'])) ?></strong></p>
                                    <p class="mb-1"><i class="bi bi-cash-coin"></i> <strong><?= number_format($reservation['total_price'], 2) ?> €</strong></p>
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
                                    <span class="badge bg-<?= $statusClass ?> mb-2">
                                        <?= $statusText ?>
                                    </span>
                                    
                                    <!-- Affichage du statut de paiement -->
                                    <?php if ($reservation['payment_status'] === 'paid' || $reservation['payment_completed'] > 0): ?>
                                        <span class="badge bg-success mb-2">Payée</span>
                                    <?php elseif ($reservation['status'] !== 'cancelled'): ?>
                                        <span class="badge bg-warning mb-2">En attente de paiement</span>
                                        <a href="payment.php?reservation_id=<?= $reservation['id'] ?>" class="btn btn-sm btn-primary w-100 mb-2">
                                            <i class="bi bi-credit-card"></i> Payer maintenant
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['status'] === 'pending' && $reservation['payment_status'] === 'unpaid'): ?>
                                        <form method="POST" action="" class="d-inline cancel-reservation-form">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            <button type="submit" name="cancel_reservation" class="btn btn-sm btn-outline-danger w-100 mt-2">
                                                <i class="bi bi-x-circle"></i> Annuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- SweetAlert2 pour confirmation d'annulation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.cancel-reservation-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Annuler la réservation ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#bfa046',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, annuler',
            cancelButtonText: 'Non'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
