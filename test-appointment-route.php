<?php

echo "=== Test de la route des rendez-vous ===\n\n";

// Simuler une requête POST
$url = 'http://localhost/appointments'; // Ajuster selon votre configuration
$data = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+1234567890',
    'subject' => 'Test de rendez-vous',
    'message' => 'Message de test',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

echo "📋 Données à envoyer :\n";
foreach ($data as $key => $value) {
    echo "   {$key}: {$value}\n";
}

echo "\n🔗 URL de test : {$url}\n";

// Test avec cURL
if (function_exists('curl_init')) {
    echo "\n✅ cURL disponible\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "❌ Erreur cURL : {$error}\n";
    } else {
        echo "✅ Réponse reçue (HTTP {$httpCode})\n";
        
        // Séparer les headers et le body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        echo "\n📋 Headers reçus :\n";
        echo $headers;
        
        echo "\n📄 Body reçu :\n";
        echo $body;
        
        // Essayer de parser le JSON
        if ($httpCode === 422) {
            $jsonData = json_decode($body, true);
            if ($jsonData && isset($jsonData['errors'])) {
                echo "\n❌ Erreurs de validation :\n";
                foreach ($jsonData['errors'] as $field => $errors) {
                    foreach ($errors as $error) {
                        echo "   {$field}: {$error}\n";
                    }
                }
            }
        }
    }
} else {
    echo "❌ cURL non disponible\n";
}

echo "\n=== Test terminé ===\n"; 