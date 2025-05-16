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
    <title>Connexion - Luxury Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container my-5">
        <div class="form-container">
            <h2 class="text-center mb-4">Connexion</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" required autofocus value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        <div class="invalid-feedback">Veuillez entrer une adresse email valide</div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" id="password" name="password" class="form-control" required>
        <div class="invalid-feedback">Veuillez entrer votre mot de passe</div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
</form>
<div class="text-center mt-3">
    <a href="register.php">Créer un compte</a>
</div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
