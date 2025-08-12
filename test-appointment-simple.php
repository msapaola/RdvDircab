<?php

echo "=== Test simple de la route des rendez-vous ===\n\n";

// Simuler une requête POST simple
$url = 'http://localhost/appointments';
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

// Test avec file_get_contents (plus simple que cURL)
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest'
        ],
        'content' => http_build_query($data),
        'timeout' => 30
    ]
]);

echo "\n📤 Envoi de la requête...\n";

try {
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Erreur lors de la requête\n";
        
        // Vérifier les erreurs HTTP
        $httpResponseHeader = $http_response_header ?? [];
        if (!empty($httpResponseHeader)) {
            echo "📋 Headers de réponse :\n";
            foreach ($httpResponseHeader as $header) {
                echo "   {$header}\n";
            }
        }
    } else {
        echo "✅ Réponse reçue\n";
        echo "📄 Contenu de la réponse :\n";
        echo $response . "\n";
        
        // Essayer de parser le JSON
        $jsonData = json_decode($response, true);
        if ($jsonData) {
            echo "\n📊 Données JSON parsées :\n";
            print_r($jsonData);
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception : {$e->getMessage()}\n";
}

echo "\n=== Test terminé ===\n"; 