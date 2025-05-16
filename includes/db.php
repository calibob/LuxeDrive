<?php
// Configuration de la base de données pour XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'luxury_car_rental');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch(PDOException $e) {
    // En production, ne pas afficher les détails de l'erreur
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        die('Une erreur est survenue lors de la connexion à la base de données.');
    } else {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

// Fonction pour fermer la connexion
function closeConnection() {
    global $pdo;
    $pdo = null;
}

// Enregistrer la fonction de fermeture
register_shutdown_function('closeConnection');
?>
