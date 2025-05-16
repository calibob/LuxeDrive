<?php
require_once dirname(__FILE__) . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/luxury-car-rental/index.php">
            <img src="/luxury-car-rental/assets/images/luxedrive-logo.svg" alt="LuxeDrive Logo" style="height:38px; margin-right:10px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'catalog.php' ? 'active' : '' ?>" 
                       href="/luxury-car-rental/catalog.php">Catalogue</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/luxury-car-rental/admin/dashboard.php">Administration</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/luxury-car-rental/client/dashboard.php">Mon Compte</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/luxury-car-rental/logout.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>" 
                           href="/luxury-car-rental/login.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>" 
                           href="/luxury-car-rental/register.php">Inscription</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
