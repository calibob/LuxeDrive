<?php
// Configuration simplifiée sans API Stripe réelle

// Constante pour le mode simulation
define('SIMULATION_MODE', true);

// Configuration de l'application
define('APP_URL', 'http://localhost/luxury-car-rental');
define('CURRENCY', 'EUR');

// Fausses clés pour la simulation (jamais utilisées pour des appels réels)
define('STRIPE_PUBLIC_KEY', 'pk_test_simulation');
define('STRIPE_SECRET_KEY', 'sk_test_simulation');
define('STRIPE_WEBHOOK_SECRET', 'whsec_simulation');
?>
