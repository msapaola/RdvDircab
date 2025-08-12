<?php

echo "=== Test de l'endpoint des rendez-vous ===\n\n";

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

// Vérifier la route
echo "\n🔍 Test de la route POST /appointments...\n";
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
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des routes :\n";
    echo "   Message : {$e->getMessage()}\n";
    exit;
}

// Simuler une requête
echo "\n🔍 Test de simulation de requête...\n";
try {
    // Créer une requête simulée
    $request = \Illuminate\Http\Request::create('/appointments', 'POST', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'subject' => 'Test de rendez-vous pour vérifier la validation',
        'message' => 'Message de test',
        'preferred_date' => date('Y-m-d', strtotime('+2 days')),
        'preferred_time' => '10:00',
        'priority' => 'normal',
    ]);
    
    // Ajouter les headers nécessaires
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    echo "✅ Requête simulée créée\n";
    
    // Vérifier la validation
    echo "\n🔍 Test de validation...\n";
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
    
    // Tester le contrôleur
    echo "\n🔍 Test du contrôleur...\n";
    try {
        $controller = new \App\Http\Controllers\PublicController();
        echo "✅ Contrôleur créé\n";
        
        // Vérifier la méthode store
        $reflection = new ReflectionClass($controller);
        if ($reflection->hasMethod('store')) {
            echo "✅ Méthode 'store' trouvée\n";
        } else {
            echo "❌ Méthode 'store' non trouvée\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur avec le contrôleur :\n";
        echo "   Message : {$e->getMessage()}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la simulation :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier la base de données
echo "\n🔍 Test de la base de données...\n";
try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier la table appointments
    $tableExists = \Illuminate\Support\Facades\Schema::hasTable('appointments');
    if ($tableExists) {
        echo "✅ Table 'appointments' existe\n";
        
        // Vérifier la structure
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('appointments');
        echo "   Colonnes : " . implode(', ', $columns) . "\n";
    } else {
        echo "❌ Table 'appointments' n'existe pas\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de base de données :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier les middlewares
echo "\n🔍 Test des middlewares...\n";
try {
    $middlewareGroups = app('router')->getMiddlewareGroups();
    $routeMiddleware = app('router')->getMiddleware();
    
    echo "✅ Middlewares disponibles :\n";
    echo "   Web : " . implode(', ', array_keys($middlewareGroups['web'] ?? [])) . "\n";
    echo "   Route : " . implode(', ', array_keys($routeMiddleware)) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des middlewares :\n";
    echo "   Message : {$e->getMessage()}\n";
}

echo "\n=== Test terminé ===\n"; 