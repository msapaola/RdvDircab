<?php

// Script de test pour vérifier la soumission du formulaire
echo "=== Test de soumission du formulaire de rendez-vous ===\n\n";

// Configuration
$url = 'https://green-wolverine-495039.hostingersite.com/appointments';

echo "1. Test de soumission avec données valides...\n";

// Données de test valides
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test de demande de rendez-vous',
    'message' => 'Ceci est un test de soumission de formulaire pour vérifier le fonctionnement.',
    'preferred_date' => date('Y-m-d', strtotime('+3 days')), // 3 jours à l'avance
    'preferred_time' => '09:00',
    'priority' => 'normal',
];

// Créer un FormData simulé
$postData = http_build_query($testData);

// Soumettre le formulaire
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
echo "   Headers:\n";
foreach (explode("\n", $headers) as $header) {
    if (trim($header)) {
        echo "     " . trim($header) . "\n";
    }
}

echo "\n   Réponse:\n";
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
        echo "   Erreurs:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "     $field: " . implode(', ', $errors) . "\n";
        }
    }
} else {
    echo "   ❌ Réponse non-JSON ou invalide\n";
    echo "   Body: " . substr($body, 0, 500) . "\n";
}

echo "\n2. Test avec données invalides (validation)...\n";

// Données invalides
$invalidData = [
    'name' => '', // Nom vide
    'email' => 'invalid-email', // Email invalide
    'phone' => '', // Téléphone vide
    'subject' => '', // Sujet vide
    'preferred_date' => date('Y-m-d', strtotime('-1 day')), // Date passée
    'preferred_time' => '25:00', // Heure invalide
    'priority' => 'invalid', // Priorité invalide
];

$postData = http_build_query($invalidData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
$responseData = json_decode($body, true);
if ($responseData && isset($responseData['errors'])) {
    echo "   ✅ Validation fonctionne - Erreurs détectées:\n";
    foreach ($responseData['errors'] as $field => $errors) {
        echo "     $field: " . implode(', ', $errors) . "\n";
    }
} else {
    echo "   ❌ Validation ne fonctionne pas comme attendu\n";
}

echo "\n3. Test de rate limiting...\n";

// Faire plusieurs soumissions rapides pour tester le rate limiting
for ($i = 1; $i <= 3; $i++) {
    $testData['email'] = "test$i@example.com";
    $postData = http_build_query($testData);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    curl_close($ch);

    echo "   Tentative $i - Code HTTP: $httpCode\n";
    $responseData = json_decode($body, true);
    if ($responseData) {
        echo "   Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
        if (isset($responseData['message'])) {
            echo "   Message: " . $responseData['message'] . "\n";
        }
    }
    echo "\n";
}

echo "=== Test terminé ===\n";
echo "\nRecommandations:\n";
echo "1. Vérifiez que le formulaire frontend envoie les données au bon format\n";
echo "2. Vérifiez que le contrôleur traite correctement les FormData\n";
echo "3. Vérifiez les logs Laravel pour plus de détails sur les erreurs\n";
echo "4. Testez manuellement le formulaire sur le site web\n"; 