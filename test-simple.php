<?php

// Script de test simple pour les rendez-vous
echo "=== TEST SIMPLE DES RENDEZ-VOUS ===\n\n";

// 1. Vérifier les fichiers essentiels
echo "1. FICHIERS ESSENTIELS\n";
echo "----------------------\n";

$files = [
    'app/Http/Controllers/PublicController.php',
    'app/Models/Appointment.php',
    'resources/js/Pages/Public/Home.jsx',
    'resources/js/Components/Forms/AppointmentForm.jsx',
    'routes/web.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file: EXISTE\n";
    } else {
        echo "✗ $file: MANQUANT\n";
    }
}

// 2. Vérifier les routes
echo "\n2. ROUTES\n";
echo "---------\n";

$routesContent = file_get_contents('routes/web.php');
if (str_contains($routesContent, 'Route::post(\'/appointments\'')) {
    echo "✓ Route POST /appointments: PRÉSENTE\n";
} else {
    echo "✗ Route POST /appointments: MANQUANTE\n";
}

if (str_contains($routesContent, 'withoutMiddleware([\\App\\Http\\Middleware\\VerifyCsrfToken::class])')) {
    echo "✓ CSRF désactivé: OUI\n";
} else {
    echo "✗ CSRF désactivé: NON\n";
}

// 3. Vérifier les logs récents
echo "\n3. LOGS RÉCENTS\n";
echo "---------------\n";

$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -5); // 5 dernières lignes
    
    echo "Dernières 5 lignes du log:\n";
    foreach ($recentLines as $line) {
        if (str_contains($line, 'appointment') || str_contains($line, 'error') || str_contains($line, '419')) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo "✗ Fichier de log non trouvé\n";
}

// 4. Test de requête cURL
echo "\n4. COMMANDE DE TEST CURL\n";
echo "------------------------\n";

$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test Subject',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '09:00',
    'priority' => 'normal'
];

$curlCommand = "curl -X POST http://localhost/appointments \\\n";
$curlCommand .= "  -H 'Content-Type: application/json' \\\n";
$curlCommand .= "  -H 'Accept: application/json' \\\n";
$curlCommand .= "  -H 'X-Requested-With: XMLHttpRequest' \\\n";
$curlCommand .= "  -d '" . json_encode($testData) . "' \\\n";
$curlCommand .= "  -v";

echo "Commande à tester:\n";
echo $curlCommand . "\n";

// 5. Instructions de débogage
echo "\n5. INSTRUCTIONS DE DÉBOGAGE\n";
echo "---------------------------\n";

echo "1. Ouvrez les outils de développement du navigateur (F12)\n";
echo "2. Allez dans l'onglet Console\n";
echo "3. Allez dans l'onglet Network\n";
echo "4. Soumettez un formulaire de rendez-vous\n";
echo "5. Vérifiez les messages dans la console\n";
echo "6. Vérifiez la requête POST dans l'onglet Network\n";
echo "7. Vérifiez la réponse du serveur\n\n";

echo "=== COMMANDES À EXÉCUTER ===\n";
echo "npm run build\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan route:clear\n";
echo "tail -f storage/logs/laravel.log\n";
echo "php artisan route:list | grep appointment\n"; 