<?php

// Script de débogage pour le formulaire de rendez-vous
echo "=== Débogage du formulaire de rendez-vous ===\n\n";

// 1. Vérifier la configuration Laravel
echo "1. Configuration Laravel:\n";
echo "   APP_ENV: " . (getenv('APP_ENV') ?: 'non défini') . "\n";
echo "   APP_DEBUG: " . (getenv('APP_DEBUG') ?: 'non défini') . "\n";
echo "   APP_URL: " . (getenv('APP_URL') ?: 'non défini') . "\n";

// 2. Vérifier les routes
echo "\n2. Routes disponibles:\n";
$routes = [
    'GET /' => 'Page d\'accueil',
    'POST /appointments' => 'Soumission de rendez-vous',
    'GET /tracking/{token}' => 'Suivi de rendez-vous',
];

foreach ($routes as $route => $description) {
    echo "   $route - $description\n";
}

// 3. Vérifier le middleware
echo "\n3. Middleware sur /appointments:\n";
echo "   - web (sessions, cookies)\n";
echo "   - throttle.appointments (rate limiting)\n";
echo "   - CSRF désactivé temporairement\n";

// 4. Test de connexion à la base de données
echo "\n4. Test de base de données:\n";
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    echo "   ✅ Connexion à la base de données réussie\n";
    
    // Vérifier la table appointments
    $stmt = $pdo->query("SHOW TABLES LIKE 'appointments'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Table 'appointments' existe\n";
        
        // Compter les rendez-vous
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM appointments");
        $count = $stmt->fetch()['count'];
        echo "   📊 Nombre de rendez-vous en base: $count\n";
    } else {
        echo "   ❌ Table 'appointments' n'existe pas\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur de base de données: " . $e->getMessage() . "\n";
}

// 5. Test de soumission simple
echo "\n5. Test de soumission simple:\n";

$testData = [
    'name' => 'Test Debug',
    'email' => 'debug@test.com',
    'phone' => '+243123456789',
    'subject' => 'Test de débogage',
    'message' => 'Test de soumission pour débogage',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

$url = 'https://green-wolverine-495039.hostingersite.com/appointments';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Debug-Script/1.0');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($error) {
    echo "   Erreur cURL: $error\n";
}

echo "   Headers de réponse:\n";
foreach (explode("\n", $headers) as $header) {
    if (trim($header) && strpos($header, ':') !== false) {
        echo "     " . trim($header) . "\n";
    }
}

echo "   Corps de la réponse:\n";
$responseData = json_decode($body, true);
if ($responseData) {
    echo "   ✅ Réponse JSON valide\n";
    echo "   Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    if (isset($responseData['message'])) {
        echo "   Message: " . $responseData['message'] . "\n";
    }
    if (isset($responseData['tracking_url'])) {
        echo "   Tracking URL: " . $responseData['tracking_url'] . "\n";
    }
    if (isset($responseData['errors'])) {
        echo "   Erreurs de validation:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "     $field: " . implode(', ', $errors) . "\n";
        }
    }
} else {
    echo "   ❌ Réponse non-JSON\n";
    echo "   Body: " . substr($body, 0, 1000) . "\n";
}

// 6. Vérifier les logs Laravel
echo "\n6. Vérification des logs:\n";
$logFiles = [
    storage_path('logs/laravel.log'),
    storage_path('logs/laravel-' . date('Y-m-d') . '.log'),
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "   📄 Log file: $logFile\n";
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -10); // 10 dernières lignes
        
        echo "   Dernières lignes:\n";
        foreach ($recentLines as $line) {
            if (trim($line)) {
                echo "     " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ❌ Log file non trouvé: $logFile\n";
    }
}

// 7. Recommandations
echo "\n7. Recommandations pour résoudre le problème:\n";
echo "   a) Vérifiez que le formulaire frontend envoie les données au bon format\n";
echo "   b) Vérifiez que le contrôleur traite correctement les FormData\n";
echo "   c) Vérifiez les permissions sur storage/logs/\n";
echo "   d) Activez temporairement APP_DEBUG=true pour plus de détails\n";
echo "   e) Vérifiez que la route /appointments est bien accessible\n";
echo "   f) Testez avec un outil comme Postman pour isoler le problème\n";

echo "\n=== Débogage terminé ===\n"; 