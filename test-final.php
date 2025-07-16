<?php

// Script de test final pour vérifier la soumission du formulaire
echo "=== Test Final de Soumission du Formulaire ===\n\n";

// Configuration
$baseUrl = 'https://green-wolverine-495039.hostingersite.com';
$appointmentsUrl = $baseUrl . '/appointments';

echo "1. Test de soumission du formulaire avec Inertia.js...\n";

// Données de test
$testData = [
    'name' => 'Test User Final',
    'email' => 'test-final@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test final de demande',
    'message' => 'Ceci est un test final de soumission de formulaire.',
    'preferred_date' => date('Y-m-d', strtotime('+3 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

// Soumettre le formulaire avec les headers Inertia.js
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $appointmentsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html, application/xhtml+xml',
    'X-Requested-With: XMLHttpRequest',
    'X-Inertia: true',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($error) {
    echo "   ❌ Erreur cURL: $error\n";
}

// Analyser la réponse
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "   📋 Headers de réponse:\n";
$headerLines = explode("\n", $headers);
foreach ($headerLines as $line) {
    if (trim($line) && strpos($line, 'HTTP/') === false) {
        echo "      " . trim($line) . "\n";
    }
}

echo "\n   📄 Corps de la réponse (premiers 500 caractères):\n";
echo "      " . substr($body, 0, 500) . "...\n";

// Vérifier si c'est une réponse Inertia.js
if (strpos($body, 'inertia') !== false || strpos($body, 'Inertia') !== false) {
    echo "   ✅ Réponse Inertia.js détectée\n";
} else {
    echo "   ⚠️  Réponse non-Inertia.js\n";
}

echo "\n2. Test d'accès à la page d'accueil...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($httpCode === 200) {
    echo "   ✅ Page d'accueil accessible\n";
} else {
    echo "   ❌ Erreur d'accès à la page d'accueil\n";
}

echo "\n3. Vérification des assets...\n";

$assets = [
    '/build/assets/app-C6d5PNWI.js',
    '/build/assets/Home-ugFMFq1W.js',
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

echo "\n=== Test terminé ===\n";
echo "💡 Résultats:\n";
echo "   - Si le code HTTP est 302 (redirection), c'est normal pour Inertia.js\n";
echo "   - Si le code HTTP est 422, il y a des erreurs de validation\n";
echo "   - Si le code HTTP est 200, la soumission a réussi\n";
echo "   - Vérifiez les logs du serveur pour plus de détails\n"; 