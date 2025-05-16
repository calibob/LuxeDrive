<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$stmt = $pdo->query("SELECT * FROM vehicles WHERE available = 1 ORDER BY created_at DESC LIMIT 6");
$vehicles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location de Voitures de Luxe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Luxury Cars</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Administration</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="client/dashboard.php">Mon Compte</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero" style="background: linear-gradient(rgba(0,0,0,0.65),rgba(0,0,0,0.65)), url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1500&q=80') center/cover no-repeat;">
        <div class="container">
            <h1 class="hero-title">L'Excellence <span class="accent">Automobile</span><br>À Votre Service</h1>
            <p class="hero-desc">Découvrez notre collection exclusive de voitures de luxe pour une expérience de conduite inoubliable.</p>
            <a href="catalog.php" class="btn hero-btn">Découvrir nos véhicules</a>
        </div>
    </header>

    <main class="container my-5">
        <!-- Véhicules vedettes -->
        <h2 class="mb-4 text-center">Nos Véhicules Vedettes</h2>
        <div class="row justify-content-center">
            <?php foreach (array_slice($vehicles, 0, 3) as $vehicle): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/vehicles/<?= htmlspecialchars($vehicle['image']) ?>"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>">
                        <div class="card-body">
                            <span class="price-badge"><?= number_format($vehicle['price_per_day'], 2) ?>€/jour</span>
                            <h5 class="card-title mt-2">
                                <?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>
                            </h5>
                            <p class="card-text">
                                <?= htmlspecialchars($vehicle['description']) ?>
                            </p>
                            <ul class="list-inline text-muted small mb-2">
                                <li class="list-inline-item"><i class="bi bi-calendar"></i> <?= htmlspecialchars($vehicle['year']) ?></li>
                                <!-- Add more details if needed -->
                            </ul>
                            <a href="#" class="btn btn-primary w-100">Voir les détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Services Premium -->
        <section id="services" class="my-5">
            <h2 class="mb-4 text-center">Nos Services Premium</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-car-front display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Véhicules Premium</h5>
                        <p>Une flotte exclusive de véhicules de luxe entretenus avec soin pour vous offrir une expérience intégrale.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-shield-check display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Assurance Complète</h5>
                        <p>Profitez d'une tranquillité d'esprit totale avec notre assurance tous risques incluse dans chaque location.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-truck display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Livraison Personnalisée</h5>
                        <p>Service de livraison et de récupération à l'adresse de votre choix, à l'heure qui vous convient.</p>
                    </div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-headset display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Support 24/7</h5>
                        <p>Notre équipe de concierges est disponible jour et nuit pour répondre à toutes vos demandes.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-gem display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Expérience VIP</h5>
                        <p>Un service personnalisé et haut de gamme pour une expérience sur-mesure.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="p-4 bg-white rounded shadow-sm h-100">
                        <i class="bi bi-gift display-5 mb-3" style="color:var(--secondary-color);"></i>
                        <h5>Programme Fidélité</h5>
                        <p>Accumulez des points à chaque location et bénéficiez d'avantages exclusifs.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Avis Clients -->
        <section class="my-5">
            <h2 class="mb-4 text-center">Ce Que Nos Clients Disent</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <div class="bg-dark text-white p-4 rounded shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sophie Laurent" class="rounded-circle me-3" width="56" height="56">
                            <div>
                                <strong>Sophie Laurent</strong><br><span class="small">PDG, Laurent Entreprises</span>
                            </div>
                        </div>
                        <div class="mb-2 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-0">“LuxeDrive a dépassé toutes mes attentes. La Lamborghini Huracán que j'ai louée était dans un état impeccable et le service de livraison était parfaitement ponctuel. Une expérience exceptionnelle que je recommande à tous les amateurs de voitures de luxe.”</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="bg-dark text-white p-4 rounded shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Marc Dubois" class="rounded-circle me-3" width="56" height="56">
                            <div>
                                <strong>Marc Dubois</strong><br><span class="small">Directeur marketing</span>
                            </div>
                        </div>
                        <div class="mb-2 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-0">“Pour l'organisation de notre événement d'entreprise, nous avons fait appel à LuxeDrive. La Ferrari Roma a fait sensation auprès de nos clients. Service irréprochable, je recommande !”</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="bg-dark text-white p-4 rounded shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Émilie Renard" class="rounded-circle me-3" width="56" height="56">
                            <div>
                                <strong>Émilie Renard</strong><br><span class="small">Influenceuse lifestyle</span>
                            </div>
                        </div>
                        <div class="mb-2 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-0">“J'ai loué une Bentley Continental GT pour mon mariage et tout était parfait. La voiture était magnifique, propre et a ajouté une touche d'élégance inoubliable à notre journée spéciale. Merci LuxeDrive !”</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="my-5 text-center">
            <div class="py-5" style="background: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1511918984145-48de785d4c4e?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat; border-radius: 18px;">
                <h2 class="text-white mb-3">Prêt à Vivre une Expérience Exceptionnelle&nbsp;?</h2>
                <p class="lead text-white mb-4">Réservez dès maintenant et vivez l'expérience ultime au volant d'une voiture de luxe.</p>
                <a href="catalog.php" class="btn hero-btn">Réserver Maintenant</a>
            </div>
        </section>


    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
