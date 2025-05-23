<?php
require_once 'includes/db.php';

// Assurez-vous que PDO est disponible
if (!isset($pdo)) {
    die("Erreur: Connexion à la base de données non disponible");
}

// Nouveau mot de passe admin
$password = "admin123";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Mettre à jour le mot de passe de l'admin
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@luxurycars.com'");
    $result = $stmt->execute([$hashedPassword]);
    
    if ($result) {
        echo "Le mot de passe admin a été mis à jour avec succès!<br>";
        echo "Email: admin@luxurycars.com<br>";
        echo "Mot de passe: admin123<br>";
        echo "<a href='login.php'>Retour à la page de connexion</a>";
    } else {
        echo "Erreur lors de la mise à jour du mot de passe admin.<br>";
        echo "Vérifiez que l'utilisateur admin existe dans la base de données.";
    }
} catch(PDOException $e) {
    echo "Erreur de base de données: " . $e->getMessage();
}
?>
