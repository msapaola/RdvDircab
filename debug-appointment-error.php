<?php

echo "=== Diagnostic des erreurs de rendez-vous ===\n\n";

// Vérifier si Laravel peut démarrer
echo "🔍 Test de démarrage de Laravel...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel démarre correctement\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du démarrage de Laravel :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
    exit;
}

// Vérifier la configuration de la base de données
echo "\n🔍 Test de la base de données...\n";
try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données réussie\n";
} catch (Exception $e) {
    echo "❌ Erreur de connexion à la base de données :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier les routes
echo "\n🔍 Test des routes...\n";
try {
    $router = app('router');
    $routes = $router->getRoutes();
    
    $appointmentRoute = null;
    foreach ($routes as $route) {
        if ($route->uri() === 'appointments' && in_array('POST', $route->methods())) {
            $appointmentRoute = $route;
            break;
        }
    }
    
    if ($appointmentRoute) {
        echo "✅ Route POST /appointments trouvée\n";
        echo "   Contrôleur : " . $appointmentRoute->getController() . "\n";
        echo "   Méthode : " . $appointmentRoute->getActionMethod() . "\n";
        echo "   Middleware : " . implode(', ', $appointmentRoute->middleware()) . "\n";
    } else {
        echo "❌ Route POST /appointments non trouvée\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des routes :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier la classe AppointmentRequest
echo "\n🔍 Test de la classe AppointmentRequest...\n";
try {
    $appointmentRequest = new \App\Http\Requests\AppointmentRequest();
    echo "✅ AppointmentRequest créé avec succès\n";
    
    $rules = $appointmentRequest->rules();
    echo "   Règles de validation : " . count($rules) . " champs\n";
    
    $messages = $appointmentRequest->messages();
    echo "   Messages d'erreur : " . count($messages) . " messages\n";
    
} catch (Exception $e) {
    echo "❌ Erreur avec AppointmentRequest :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
}

// Vérifier le contrôleur PublicController
echo "\n🔍 Test du contrôleur PublicController...\n";
try {
    $controller = new \App\Http\Controllers\PublicController();
    echo "✅ PublicController créé avec succès\n";
    
    // Vérifier la méthode store
    $reflection = new ReflectionClass($controller);
    if ($reflection->hasMethod('store')) {
        echo "✅ Méthode 'store' trouvée\n";
    } else {
        echo "❌ Méthode 'store' non trouvée\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur avec PublicController :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
}

// Test de validation simple
echo "\n🔍 Test de validation simple...\n";
try {
    $data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'subject' => 'Test de rendez-vous pour vérifier la validation',
        'message' => 'Message de test',
        'preferred_date' => date('Y-m-d', strtotime('+2 days')),
        'preferred_time' => '10:00',
        'priority' => 'normal',
    ];
    
    $validator = \Illuminate\Support\Facades\Validator::make($data, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'subject' => 'required|string|min:10|max:500',
        'message' => 'nullable|string|max:2000',
        'preferred_date' => 'required|date|after:today',
        'preferred_time' => 'required|date_format:H:i',
        'priority' => 'required|in:normal,urgent,official',
    ]);
    
    if ($validator->fails()) {
        echo "❌ Validation échouée :\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - {$error}\n";
        }
    } else {
        echo "✅ Validation réussie\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la validation :\n";
    echo "   Message : {$e->getMessage()}\n";
}

echo "\n=== Diagnostic terminé ===\n"; 