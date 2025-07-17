<?php

// Script de test pour vérifier que le problème CSRF est résolu
echo "=== Test de résolution du problème CSRF ===\n\n";

// Configuration
$url = 'https://green-wolverine-495039.hostingersite.com/appointments';

echo "1. Test de soumission SANS token CSRF...\n";

// Données de test
$testData = [
    'name' => 'Test CSRF Fix',
    'email' => 'test-csrf@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test de résolution CSRF',
    'message' => 'Ceci est un test pour vérifier que le problème CSRF est résolu.',
    'preferred_date' => date('Y-m-d', strtotime('+3 days')),
    'preferred_time' => '11:00',
    'priority' => 'normal',
];

// Soumettre le formulaire SANS token CSRF
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'CSRF-Test/1.0');
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

// Analyser la réponse
$responseData = json_decode($body, true);

if ($httpCode === 419) {
    echo "   ❌ Erreur CSRF toujours présente (419)\n";
    echo "   Le problème CSRF n'est pas résolu\n";
} elseif ($httpCode === 200 || $httpCode === 201) {
    echo "   ✅ Succès ! Plus d'erreur CSRF\n";
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

echo "\n2. Test avec données valides...\n";

// Données valides pour un test complet
$validData = [
    'name' => 'Jean Dupont',
    'email' => 'jean.dupont@example.com',
    'phone' => '+243900000000',
    'subject' => 'Demande d\'audience pour projet urbain',
    'message' => 'Je souhaite demander une audience pour discuter d\'un projet urbain important pour notre quartier.',
    'preferred_date' => date('Y-m-d', strtotime('+5 days')),
    'preferred_time' => '14:00',
    'priority' => 'normal',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($validData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'CSRF-Test/1.0');
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
if ($responseData) {
    if (isset($responseData['success']) && $responseData['success']) {
        echo "   ✅ Soumission réussie !\n";
        echo "   Message: " . $responseData['message'] . "\n";
        if (isset($responseData['tracking_url'])) {
            echo "   Tracking URL: " . $responseData['tracking_url'] . "\n";
        }
    } else {
        echo "   ⚠️ Soumission échouée\n";
        if (isset($responseData['message'])) {
            echo "   Message: " . $responseData['message'] . "\n";
        }
        if (isset($responseData['errors'])) {
            echo "   Erreurs:\n";
            foreach ($responseData['errors'] as $field => $errors) {
                echo "     $field: " . implode(', ', $errors) . "\n";
            }
        }
    }
}

echo "\n3. Vérification de la route...\n";

// Tester l'accès à la route
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'CSRF-Test/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP pour GET: $httpCode\n";
if ($httpCode === 405) {
    echo "   ✅ Route accessible (405 = Method Not Allowed pour GET, normal)\n";
} else {
    echo "   ⚠️ Code HTTP inattendu pour GET: $httpCode\n";
}

echo "\n=== Résumé ===\n";
if ($httpCode === 419) {
    echo "❌ Le problème CSRF persiste. Vérifiez la configuration des routes.\n";
} else {
    echo "✅ Le problème CSRF semble résolu !\n";
    echo "🎉 Le formulaire devrait maintenant fonctionner correctement.\n";
}

echo "\nProchaines étapes:\n";
echo "1. Testez le formulaire sur le site web\n";
echo "2. Vérifiez que les assets sont bien uploadés\n";
echo "3. Vérifiez la console du navigateur pour d'autres erreurs\n"; 