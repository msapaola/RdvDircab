<?php

// Script de test pour diagnostiquer le problème de soumission du formulaire
echo "=== Test de soumission du formulaire ===\n\n";

// Configuration
$url = 'https://green-wolverine-495039.hostingersite.com/appointments';
$csrfUrl = 'https://green-wolverine-495039.hostingersite.com/sanctum/csrf-cookie';

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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($httpCode === 200) {
    echo "   ✅ Token CSRF récupéré avec succès\n";
} else {
    echo "   ❌ Erreur lors de la récupération du token CSRF\n";
}

echo "\n2. Test de soumission du formulaire...\n";

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
curl_setopt($ch, CURLOPT_URL, $url);
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

echo "   📋 Headers de réponse:\n";
$headerLines = explode("\n", $headers);
foreach ($headerLines as $line) {
    if (trim($line)) {
        echo "      " . trim($line) . "\n";
    }
}

echo "\n   📄 Corps de la réponse:\n";
$responseData = json_decode($body, true);
if ($responseData) {
    echo "      " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "      " . $body . "\n";
}

echo "\n3. Test d'accès à la page d'accueil...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://green-wolverine-495039.hostingersite.com/');
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

// Nettoyer
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n=== Test terminé ===\n";
echo "💡 Suggestions:\n";
echo "   1. Vérifiez les logs d'erreur du serveur\n";
echo "   2. Vérifiez la configuration PHP (memory_limit, max_execution_time)\n";
echo "   3. Vérifiez que la base de données est accessible\n";
echo "   4. Vérifiez les permissions des dossiers de stockage\n"; 