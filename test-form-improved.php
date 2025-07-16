<?php

// Script de test amélioré pour diagnostiquer le problème CSRF
echo "=== Test de soumission du formulaire (Version améliorée) ===\n\n";

// Configuration
$baseUrl = 'https://green-wolverine-495039.hostingersite.com';
$csrfUrl = $baseUrl . '/sanctum/csrf-cookie';
$appointmentsUrl = $baseUrl . '/appointments';

echo "1. Test de récupération du token CSRF...\n";

// Récupérer le token CSRF
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $csrfUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($httpCode === 204) {
    echo "   ✅ Token CSRF récupéré avec succès (204 = No Content)\n";
} else {
    echo "   ❌ Erreur lors de la récupération du token CSRF\n";
}

echo "\n2. Test de soumission du formulaire (sans CSRF)...\n";

// Données de test
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test de demande',
    'message' => 'Ceci est un test de soumission de formulaire.',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '09:00',
    'priority' => 'normal',
];

// Soumettre le formulaire
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $appointmentsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
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

echo "   📄 Corps de la réponse:\n";
$responseData = json_decode($body, true);
if ($responseData) {
    echo "      " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "      " . $body . "\n";
}

echo "\n3. Test avec données de formulaire (multipart/form-data)...\n";

// Test avec données de formulaire standard
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $appointmentsUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "   📄 Corps de la réponse:\n";
$responseData = json_decode($body, true);
if ($responseData) {
    echo "      " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "      " . $body . "\n";
}

echo "\n4. Vérification des cookies...\n";
if (file_exists('cookies.txt')) {
    $cookies = file_get_contents('cookies.txt');
    echo "   📋 Cookies stockés:\n";
    echo "      " . $cookies . "\n";
} else {
    echo "   ❌ Aucun cookie stocké\n";
}

// Nettoyer
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n=== Test terminé ===\n";
echo "💡 Analyse:\n";
echo "   - Si le code 419 persiste, le problème vient de la configuration CSRF\n";
echo "   - Si le code 200/201 apparaît, le problème vient du frontend\n";
echo "   - Vérifiez que les routes sont bien exclues dans VerifyCsrfToken\n"; 