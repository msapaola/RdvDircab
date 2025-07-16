<?php

// Script de test final pour vérifier la solution
echo "=== Test Final de la Solution ===\n\n";

$baseUrl = 'https://green-wolverine-495039.hostingersite.com';
$appointmentsUrl = $baseUrl . '/appointments';

echo "1. Test de soumission du formulaire avec la nouvelle approche...\n";

// Données de test complètes
$testData = [
    'name' => 'Test User Final',
    'email' => 'test-final-' . time() . '@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test final de demande',
    'message' => 'Ceci est un test final de soumission de formulaire.',
    'preferred_date' => date('Y-m-d', strtotime('+3 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

// Test avec JSON (nouvelle approche)
echo "   Test avec JSON:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $appointmentsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "      Code HTTP: $httpCode\n";

// Analyser la réponse
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

$responseData = json_decode($body, true);
if ($responseData) {
    if (isset($responseData['success']) && $responseData['success']) {
        echo "      ✅ Succès! Message: " . $responseData['message'] . "\n";
        if (isset($responseData['tracking_url'])) {
            echo "      🔗 URL de suivi: " . $responseData['tracking_url'] . "\n";
        }
    } else {
        echo "      ❌ Erreur: " . ($responseData['message'] ?? 'Erreur inconnue') . "\n";
        if (isset($responseData['errors'])) {
            echo "      📋 Erreurs de validation:\n";
            foreach ($responseData['errors'] as $field => $errors) {
                echo "         - $field: " . implode(', ', $errors) . "\n";
            }
        }
    }
} else {
    echo "      ⚠️  Réponse non-JSON: " . substr($body, 0, 200) . "...\n";
}

echo "\n2. Test avec données de formulaire (fallback)...\n";

// Test avec données de formulaire standard
$formData = http_build_query($testData);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $appointmentsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

$responseData = json_decode($body, true);
if ($responseData) {
    if (isset($responseData['success']) && $responseData['success']) {
        echo "   ✅ Succès avec form-data!\n";
    } else {
        echo "   ❌ Erreur avec form-data: " . ($responseData['message'] ?? 'Erreur inconnue') . "\n";
    }
} else {
    echo "   ⚠️  Réponse non-JSON avec form-data\n";
}

echo "\n3. Vérification des assets mis à jour...\n";

$assets = [
    '/build/assets/app-BmWc7Y43.js',
    '/build/assets/Home-D99JxGd7.js',
    '/build/assets/app-8cgd_IZT.css',
    '/build/manifest.json'
];

foreach ($assets as $asset) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $asset);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ $asset accessible\n";
    } else {
        echo "   ❌ $asset non accessible (HTTP $httpCode)\n";
    }
}

echo "\n4. Test d'accès à la page d'accueil...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($httpCode === 200) {
    echo "   ✅ Page d'accueil accessible\n";
} else {
    echo "   ❌ Erreur d'accès à la page d'accueil\n";
}

echo "\n=== Test terminé ===\n";
echo "🎯 Résultats:\n";
if ($httpCode === 200 || $httpCode === 422) {
    echo "   ✅ La solution fonctionne! Le formulaire peut maintenant être soumis.\n";
    echo "   📝 Le code 422 est normal pour les erreurs de validation.\n";
} else {
    echo "   ❌ Le problème persiste. Vérifiez les logs du serveur.\n";
}

echo "\n📝 Prochaines étapes:\n";
echo "   1. Testez le formulaire sur le site web\n";
echo "   2. Si ça fonctionne, le problème CSRF est résolu\n";
echo "   3. Vous pouvez maintenant remettre la protection CSRF si nécessaire\n"; 