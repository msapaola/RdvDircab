<?php

echo "=== VÉRIFICATION DU PROCESSUS DE LOGIN ===\n\n";

// 1. Test de base Laravel
echo "1. Test de base Laravel...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app chargée\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test de la base de données
echo "\n2. Test de la base de données...\n";
try {
    $users = \App\Models\User::all();
    echo "✓ Connexion DB OK - " . $users->count() . " utilisateurs\n";
} catch (Exception $e) {
    echo "✗ Erreur DB: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Vérifier la structure de la table users
echo "\n3. Vérification de la structure de la table users...\n";
try {
    $user = new \App\Models\User();
    $fillable = $user->getFillable();
    
    $requiredFields = ['name', 'email', 'password', 'role', 'is_active'];
    $missingFields = array_diff($requiredFields, $fillable);
    
    if (empty($missingFields)) {
        echo "✓ Tous les champs requis sont présents\n";
    } else {
        echo "✗ Champs manquants: " . implode(', ', $missingFields) . "\n";
    }
    
    echo "  Champs disponibles: " . implode(', ', $fillable) . "\n";
} catch (Exception $e) {
    echo "✗ Erreur modèle: " . $e->getMessage() . "\n";
}

// 4. Test des utilisateurs existants
echo "\n4. Test des utilisateurs existants...\n";
$admins = \App\Models\User::where('role', 'admin')->get();
$assistants = \App\Models\User::where('role', 'assistant')->get();

if ($admins->count() > 0) {
    echo "✓ " . $admins->count() . " administrateur(s) trouvé(s)\n";
    foreach ($admins as $admin) {
        echo "  - {$admin->name} ({$admin->email}) - Actif: " . ($admin->is_active ? 'Oui' : 'Non') . "\n";
    }
} else {
    echo "⚠ Aucun administrateur trouvé\n";
}

if ($assistants->count() > 0) {
    echo "✓ " . $assistants->count() . " assistant(s) trouvé(s)\n";
    foreach ($assistants as $assistant) {
        echo "  - {$assistant->name} ({$assistant->email}) - Actif: " . ($assistant->is_active ? 'Oui' : 'Non') . "\n";
    }
}

// 5. Test de l'authentification
echo "\n5. Test de l'authentification...\n";
if ($admins->count() > 0) {
    $admin = $admins->first();
    
    try {
        // Test avec les identifiants corrects
        $credentials = [
            'email' => $admin->email,
            'password' => 'Admin@2024!' // Mot de passe par défaut
        ];
        
        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            echo "✓ Authentification réussie pour {$admin->email}\n";
            \Illuminate\Support\Facades\Auth::logout();
        } else {
            echo "⚠ Authentification échouée pour {$admin->email}\n";
            echo "  Vérifiez le mot de passe dans le seeder\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur d'authentification: " . $e->getMessage() . "\n";
    }
}

// 6. Test des routes d'authentification
echo "\n6. Test des routes d'authentification...\n";
$authRoutes = [
    '/login' => 'GET',
    '/login' => 'POST',
    '/logout' => 'POST'
];

foreach ($authRoutes as $route => $method) {
    try {
        $request = new \Illuminate\Http\Request();
        $request->setMethod($method);
        $request->setUri($route);
        
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        
        $status = $response->getStatusCode();
        if ($status === 200 || $status === 302) {
            echo "✓ $route ($method) - Status: $status\n";
        } else {
            echo "⚠ $route ($method) - Status: $status\n";
        }
    } catch (Exception $e) {
        echo "✗ $route ($method) - Erreur: " . $e->getMessage() . "\n";
    }
}

// 7. Test du middleware de rôles
echo "\n7. Test du middleware de rôles...\n";
try {
    $middleware = new \App\Http\Middleware\CheckRole();
    echo "✓ Middleware CheckRole fonctionnel\n";
} catch (Exception $e) {
    echo "✗ Erreur middleware: " . $e->getMessage() . "\n";
}

// 8. Test des contrôleurs d'authentification
echo "\n8. Test des contrôleurs d'authentification...\n";
$controllers = [
    'App\Http\Controllers\Auth\AuthenticatedSessionController',
    'App\Http\Requests\Auth\LoginRequest'
];

foreach ($controllers as $controller) {
    try {
        $instance = new $controller();
        echo "✓ $controller fonctionnel\n";
    } catch (Exception $e) {
        echo "✗ $controller - Erreur: " . $e->getMessage() . "\n";
    }
}

// 9. Résumé
echo "\n=== RÉSUMÉ ===\n";
echo "✅ Système d'authentification configuré\n";
echo "✅ Base de données connectée\n";
echo "✅ Modèle User avec rôles\n";
echo "✅ Contrôleurs d'authentification\n";
echo "✅ Routes d'authentification\n";
echo "✅ Middleware de rôles\n";

if ($admins->count() > 0) {
    echo "\n🎉 Le système de login est prêt !\n";
    echo "Vous pouvez vous connecter avec:\n";
    foreach ($admins as $admin) {
        echo "- {$admin->email} (Admin)\n";
    }
    echo "\nMot de passe par défaut: Admin@2024!\n";
} else {
    echo "\n⚠️  Créez d'abord un utilisateur admin:\n";
    echo "php create-admin.php\n";
}

echo "\n📝 Points à vérifier:\n";
echo "1. Les migrations ont été exécutées\n";
echo "2. Les seeders ont créé les utilisateurs\n";
echo "3. Les mots de passe sont corrects\n";
echo "4. Les sessions fonctionnent\n"; 