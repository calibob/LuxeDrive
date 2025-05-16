<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Tous les champs sont requis';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé';
        } else {
            if (registerUser($email, $password, $name)) {
                $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Une erreur est survenue lors de l\'inscription';
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
    <title>Inscription - Luxury Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container my-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Inscription</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <br>
                    <a href="login.php" class="alert-link">Se connecter</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Nom complet</label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           required 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    <div class="invalid-feedback">
                        Veuillez entrer votre nom
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <div class="invalid-feedback">
                        Veuillez entrer une adresse email valide
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="8">
                    <div class="invalid-feedback">
                        Le mot de passe doit contenir au moins 8 caractères
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required>
                    <div class="invalid-feedback">
                        Veuillez confirmer votre mot de passe
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
            </form>

            <div class="text-center mt-3">
                <p>Déjà inscrit ? <a href="login.php">Connectez-vous</a></p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

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
                
                // Vérification personnalisée des mots de passe
                const password = form.querySelector('#password')
                const confirmPassword = form.querySelector('#confirm_password')
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas')
                } else {
                    confirmPassword.setCustomValidity('')
                }
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
