<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Tous les champs sont requis';
    } else {
        if (loginUser($email, $password)) {
            header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'client/dashboard.php'));
            exit();
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LuxeDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body style="background: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1511918984145-48de785d4c4e?auto=format&fit=crop&w=1500&q=80') center/cover no-repeat; min-height:100vh;">
    <?php include 'includes/header.php'; ?>

    <div class="container d-flex align-items-center justify-content-center" style="min-height: 90vh;">
        <div class="w-100" style="max-width: 420px;">
            <div class="text-center mb-4">
                <img src="assets/images/luxedrive-logo.svg" alt="LuxeDrive Logo" style="height: 56px; margin-bottom: 10px;">
                <h2 class="mb-2" style="font-family: var(--font-heading); font-weight:700; color:var(--secondary-color);">Connexion</h2>
            </div>

            <!-- Alertes dynamiques -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error']) || $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= !empty($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation bg-white p-4 rounded shadow-sm" style="backdrop-filter: blur(2px);" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Votre email" required autofocus>
                        <div class="invalid-feedback">Veuillez saisir votre email.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                        <div class="invalid-feedback">Veuillez saisir votre mot de passe.</div>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn hero-btn">Se connecter</button>
                </div>
                <div class="text-center">
                    <a href="register.php" class="small">Créer un compte</a> &middot; <a href="index.php" class="small">Retour à l'accueil</a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Validation Bootstrap
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>
</html>
