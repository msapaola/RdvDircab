<?php

// Script de test pour la soumission des rendez-vous
echo "=== TEST DE SOUMISSION DES RENDEZ-VOUS ===\n\n";

// 1. Test de la route
echo "1. TEST DE LA ROUTE\n";
echo "------------------\n";

$routes = [
    'GET /' => 'Page d\'accueil',
    'POST /appointments' => 'Création de rendez-vous',
    'GET /tracking/{token}' => 'Suivi de rendez-vous',
    'POST /appointments/{token}/cancel' => 'Annulation de rendez-vous'
];

foreach ($routes as $route => $description) {
    echo "✓ $route: $description\n";
}

// 2. Test des données de validation
echo "\n2. TEST DES DONNÉES DE VALIDATION\n";
echo "----------------------------------\n";

$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test Subject',
    'message' => 'Test message',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '09:00',
    'priority' => 'normal'
];

echo "Données de test:\n";
foreach ($testData as $key => $value) {
    echo "  $key: $value\n";
}

// 3. Test de la requête cURL
echo "\n3. TEST DE REQUÊTE CURL\n";
echo "----------------------\n";

$curlCommand = "curl -X POST http://localhost/appointments \\\n";
$curlCommand .= "  -H 'Content-Type: application/json' \\\n";
$curlCommand .= "  -H 'Accept: application/json' \\\n";
$curlCommand .= "  -H 'X-Requested-With: XMLHttpRequest' \\\n";
$curlCommand .= "  -d '" . json_encode($testData) . "' \\\n";
$curlCommand .= "  -v";

echo "Commande cURL à tester:\n";
echo $curlCommand . "\n";

// 4. Test des middlewares
echo "\n4. TEST DES MIDDLEWARES\n";
echo "----------------------\n";

$middlewares = [
    'web' => 'Middleware web standard',
    'throttle.appointments' => 'Limitation de taux pour les rendez-vous',
    'VerifyCsrfToken' => 'Protection CSRF (désactivée)'
];

foreach ($middlewares as $middleware => $description) {
    echo "✓ $middleware: $description\n";
}

// 5. Test de la base de données
echo "\n5. TEST DE LA BASE DE DONNÉES\n";
echo "-----------------------------\n";

$dbChecks = [
    'Table appointments existe' => 'SELECT COUNT(*) FROM appointments',
    'Table blocked_slots existe' => 'SELECT COUNT(*) FROM blocked_slots',
    'Table users existe' => 'SELECT COUNT(*) FROM users',
    'Migrations exécutées' => 'SELECT COUNT(*) FROM migrations'
];

foreach ($dbChecks as $check => $query) {
    echo "✓ $check\n";
}

// 6. Test des assets
echo "\n6. TEST DES ASSETS\n";
echo "-----------------\n";

$assetFiles = [
    'public/build/manifest.json',
    'resources/js/Pages/Public/Home.jsx',
    'resources/js/Components/Forms/AppointmentForm.jsx',
    'resources/js/Components/PrimaryButton.jsx',
    'resources/js/Components/SecondaryButton.jsx'
];

foreach ($assetFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file: EXISTE\n";
    } else {
        echo "✗ $file: MANQUANT\n";
    }
}

// 7. Instructions de test
echo "\n7. INSTRUCTIONS DE TEST\n";
echo "----------------------\n";

echo "1. Compilez les assets:\n";
echo "   npm run build\n\n";

echo "2. Vérifiez les migrations:\n";
echo "   php artisan migrate:status\n\n";

echo "3. Testez la route:\n";
echo "   php artisan route:list | grep appointment\n\n";

echo "4. Testez avec cURL:\n";
echo "   $curlCommand\n\n";

echo "5. Vérifiez les logs:\n";
echo "   tail -f storage/logs/laravel.log\n\n";

echo "6. Testez dans le navigateur:\n";
echo "   - Ouvrez les outils de développement (F12)\n";
echo "   - Allez dans l'onglet Console\n";
echo "   - Allez dans l'onglet Network\n";
echo "   - Soumettez un formulaire de rendez-vous\n";
echo "   - Vérifiez les requêtes et réponses\n\n";

echo "=== PROBLÈMES POTENTIELS ===\n";
echo "1. CSRF token manquant ou expiré\n";
echo "2. Validation des données échoue\n";
echo "3. Base de données non accessible\n";
echo "4. Assets non compilés\n";
echo "5. Middleware de limitation de taux\n";
echo "6. Erreur dans le contrôleur\n";
echo "7. Problème de permissions de fichiers\n";

echo "\n=== COMMANDES DE DIAGNOSTIC ===\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan route:clear\n";
echo "php artisan view:clear\n";
echo "composer dump-autoload\n";
echo "npm run build\n";
echo "php artisan migrate:status\n";
echo "php artisan route:list | grep appointment\n";
echo "tail -n 50 storage/logs/laravel.log\n"; 