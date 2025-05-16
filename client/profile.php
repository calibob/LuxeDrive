<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Traiter la mise à jour du profil
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $error = 'Cet email est déjà utilisé par un autre utilisateur';
    } else {
        // Mise à jour du nom et de l'email
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $_SESSION['user_id']]);

        // Mise à jour du mot de passe si demandé
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error = 'Les nouveaux mots de passe ne correspondent pas';
            } else {
                // Vérifier l'ancien mot de passe
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $current_hash = $stmt->fetchColumn();

                if (password_verify($current_password, $current_hash)) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $_SESSION['user_id']]);
                    $success = 'Profil mis à jour avec succès';
                } else {
                    $error = 'Mot de passe actuel incorrect';
                }
            }
        } else {
            $success = 'Profil mis à jour avec succès';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Luxury Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container my-5">
        <div class="row">
            <div class="col-md-3">
                <!-- Menu latéral -->
                <div class="list-group mb-4">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">
                        Mes Réservations
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        Mon Profil
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <h2 class="mb-4">Mon Profil</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       required>
                            </div>

                            <hr>
                            <h5>Changer le mot de passe</h5>
                            <p class="text-muted small">Laissez vide si vous ne souhaitez pas changer votre mot de passe</p>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="current_password" 
                                       name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password">
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password">
                            </div>

                            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            })
        })
    })()
    </script>
</body>
</html>
