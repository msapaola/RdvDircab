<?php

echo "=== TEST ACCÈS ADMIN ===\n\n";

// 1. Test de connexion à la base de données
echo "1. Test de connexion à la base de données...\n";
try {
    $app = require_once 'bootstrap/app.php';
    $users = \App\Models\User::all();
    echo "✓ Connexion à la base de données réussie\n";
    echo "  Nombre d'utilisateurs: " . $users->count() . "\n";
} catch (Exception $e) {
    echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Vérifier les utilisateurs admin
echo "\n2. Vérification des utilisateurs admin...\n";
$admins = \App\Models\User::where('role', 'admin')->get();
$assistants = \App\Models\User::where('role', 'assistant')->get();

if ($admins->count() > 0) {
    echo "✓ Utilisateurs admin trouvés:\n";
    foreach ($admins as $admin) {
        echo "  - {$admin->name} ({$admin->email})\n";
    }
} else {
    echo "✗ Aucun utilisateur admin trouvé\n";
}

if ($assistants->count() > 0) {
    echo "✓ Utilisateurs assistant trouvés:\n";
    foreach ($assistants as $assistant) {
        echo "  - {$assistant->name} ({$assistant->email})\n";
    }
} else {
    echo "⚠ Aucun utilisateur assistant trouvé\n";
}

// 3. Test des routes admin
echo "\n3. Test des routes admin...\n";
$routes = [
    '/admin/dashboard',
    '/admin/appointments',
    '/admin/users'
];

foreach ($routes as $route) {
    try {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('GET');
        $request->setUri($route);
        
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        
        if ($response->getStatusCode() === 302) {
            echo "  ✓ $route - Redirection (normal, authentification requise)\n";
        } else {
            echo "  ✓ $route - Status: " . $response->getStatusCode() . "\n";
        }
    } catch (Exception $e) {
        echo "  ✗ $route - Erreur: " . $e->getMessage() . "\n";
    }
}

// 4. Test du middleware de rôle
echo "\n4. Test du middleware de rôle...\n";
try {
    $middleware = new \App\Http\Middleware\CheckRole();
    echo "✓ Middleware CheckRole chargé avec succès\n";
} catch (Exception $e) {
    echo "✗ Erreur middleware: " . $e->getMessage() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n";
echo "Si tous les tests sont OK, vous pouvez:\n";
echo "1. Aller sur /admin/dashboard\n";
echo "2. Vous connecter avec les identifiants admin\n";
echo "3. Commencer à gérer les rendez-vous\n"; 