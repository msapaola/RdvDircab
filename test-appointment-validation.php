<?php

require_once 'vendor/autoload.php';

echo "=== Test de validation des rendez-vous ===\n\n";

// Simuler une requête de rendez-vous
$requestData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+1234567890',
    'subject' => 'Test de rendez-vous pour vérifier la validation',
    'message' => 'Ceci est un message de test pour vérifier que la validation fonctionne correctement.',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
    'attachments' => [],
    // 'g-recaptcha-response' => 'test', // Optionnel maintenant
];

echo "📋 Données de test :\n";
foreach ($requestData as $key => $value) {
    echo "   {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
}

echo "\n=== Test de validation avec AppointmentRequest ===\n";

try {
    // Créer une instance de la requête
    $appointmentRequest = new \App\Http\Requests\AppointmentRequest();
    
    // Simuler le contexte Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $appointmentRequest->setContainer($app);
    $appointmentRequest->setUserResolver(function() { return null; });
    $appointmentRequest->merge($requestData);
    
    echo "✅ AppointmentRequest créé avec succès\n";
    
    // Récupérer les règles de validation
    $rules = $appointmentRequest->rules();
    echo "📏 Règles de validation :\n";
    foreach ($rules as $field => $rule) {
        if (is_array($rule)) {
            echo "   {$field}: " . implode('|', $rule) . "\n";
        } else {
            echo "   {$field}: {$rule}\n";
        }
    }
    
    // Récupérer les messages d'erreur
    $messages = $appointmentRequest->messages();
    echo "\n💬 Messages d'erreur :\n";
    foreach ($messages as $key => $message) {
        echo "   {$key}: {$message}\n";
    }
    
    // Tester la validation
    echo "\n🔍 Test de validation...\n";
    
    $validator = \Illuminate\Support\Facades\Validator::make(
        $requestData, 
        $rules, 
        $messages
    );
    
    if ($validator->fails()) {
        echo "❌ Validation échouée :\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - {$error}\n";
        }
    } else {
        echo "✅ Validation réussie !\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
    echo "   Trace :\n";
    foreach ($e->getTrace() as $i => $trace) {
        if ($i < 5) { // Limiter l'affichage
            echo "     #{$i} {$trace['file']}:{$trace['line']} {$trace['function']}()\n";
        }
    }
}

echo "\n=== Test de validation avec Validator direct ===\n";

try {
    // Test avec Validator::make directement
    $validator = \Illuminate\Support\Facades\Validator::make($requestData, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'subject' => 'required|string|min:10|max:500',
        'message' => 'nullable|string|max:2000',
        'preferred_date' => 'required|date|after:today',
        'preferred_time' => 'required|date_format:H:i',
        'priority' => 'required|in:normal,urgent,official',
        'attachments' => 'nullable|array|max:5',
        'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
    ]);
    
    if ($validator->fails()) {
        echo "❌ Validation directe échouée :\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - {$error}\n";
        }
    } else {
        echo "✅ Validation directe réussie !\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la validation directe :\n";
    echo "   Message : {$e->getMessage()}\n";
}

echo "\n=== Test terminé ===\n"; 