<?php

// Test simple de la route /appointments
echo "=== Test Simple de la Route /appointments ===\n\n";

$url = 'https://green-wolverine-495039.hostingersite.com/appointments';

// Données de test
$data = [
    'name' => 'Test Simple',
    'email' => 'test@simple.com',
    'phone' => '+243123456789',
    'subject' => 'Test simple',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

echo "1. Test de la route avec données valides...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Test-Simple/1.0');
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

// Analyser la réponse
$responseData = json_decode($body, true);

if ($httpCode === 419) {
    echo "   ❌ Erreur CSRF (419)\n";
    echo "   Le problème CSRF persiste\n";
} elseif ($httpCode === 200 || $httpCode === 201) {
    echo "   ✅ Succès !\n";
    if ($responseData && isset($responseData['success'])) {
        echo "   Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
        if (isset($responseData['message'])) {
            echo "   Message: " . $responseData['message'] . "\n";
        }
        if (isset($responseData['tracking_url'])) {
            echo "   Tracking URL: " . $responseData['tracking_url'] . "\n";
        }
    }
} elseif ($httpCode === 422) {
    echo "   ✅ Plus d'erreur CSRF ! Erreur de validation normale\n";
    if ($responseData && isset($responseData['errors'])) {
        echo "   Erreurs de validation:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "     $field: " . implode(', ', $errors) . "\n";
        }
    }
} else {
    echo "   ⚠️ Code HTTP inattendu: $httpCode\n";
    echo "   Réponse: " . substr($body, 0, 500) . "\n";
}

echo "\n2. Vérification des headers de réponse...\n";
$headerLines = explode("\n", $headers);
foreach ($headerLines as $line) {
    if (trim($line) && strpos($line, ':') !== false) {
        echo "   " . trim($line) . "\n";
    }
}

echo "\n=== Test terminé ===\n";

if ($httpCode === 419) {
    echo "\n❌ Le problème CSRF persiste.\n";
    echo "Actions recommandées:\n";
    echo "1. Vérifiez que le fichier routes/web.php est bien uploadé\n";
    echo "2. Videz les caches: php artisan config:clear && php artisan route:clear\n";
    echo "3. Redémarrez le serveur web\n";
    echo "4. Vérifiez les logs Laravel\n";
} else {
    echo "\n✅ Le problème CSRF semble résolu !\n";
    echo "Le formulaire devrait maintenant fonctionner correctement.\n";
} 