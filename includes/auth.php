<?php
require_once 'db.php';
require_once 'functions.php';

function registerUser($email, $password, $name) {
    global $pdo;
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, 'client')");
        return $stmt->execute([$email, $hashedPassword, $name]);
    } catch(PDOException $e) {
        return false;
    }
}

function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            return true;
        }
    } catch(PDOException $e) {
        return false;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
