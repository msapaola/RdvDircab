<?php

echo "=== TEST SERVEUR LOGIN ===\n\n";

// 1. Test de base
echo "1. Test de base...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app chargée\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test des routes
echo "\n2. Test des routes...\n";
$routes = [
    '/test',
    '/login-simple',
    '/login'
];

foreach ($routes as $route) {
    try {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('GET');
        $request->setUri($route);
        
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        
        $status = $response->getStatusCode();
        $contentType = $response->headers->get('Content-Type');
        
        echo "  $route:\n";
        echo "    Status: $status\n";
        echo "    Content-Type: $contentType\n";
        
        if ($status === 200) {
            echo "    ✓ Page accessible\n";
        } elseif ($status === 302) {
            echo "    ⚠ Redirection (normal pour certaines pages)\n";
        } else {
            echo "    ✗ Problème: Status $status\n";
        }
        
    } catch (Exception $e) {
        echo "  $route: ✗ Erreur - " . $e->getMessage() . "\n";
    }
}

// 3. Test des assets
echo "\n3. Test des assets...\n";
$assetFiles = [
    'public/build/manifest.json',
    'public/build/assets/app-*.js',
    'public/build/assets/app-*.css'
];

foreach ($assetFiles as $pattern) {
    $files = glob($pattern);
    if (!empty($files)) {
        foreach ($files as $file) {
            echo "  ✓ $file existe\n";
        }
    } else {
        echo "  ✗ Aucun fichier trouvé pour: $pattern\n";
    }
}

// 4. Test du manifest
echo "\n4. Test du manifest...\n";
if (file_exists('public/build/manifest.json')) {
    $manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
    if ($manifest) {
        echo "  ✓ Manifest valide\n";
        echo "  Entrées dans le manifest: " . count($manifest) . "\n";
        
        // Vérifier les entrées importantes
        $importantEntries = ['app.jsx', 'app.css'];
        foreach ($importantEntries as $entry) {
            if (isset($manifest[$entry])) {
                echo "  ✓ $entry trouvé dans le manifest\n";
            } else {
                echo "  ✗ $entry manquant dans le manifest\n";
            }
        }
    } else {
        echo "  ✗ Manifest invalide (JSON)\n";
    }
} else {
    echo "  ✗ Manifest manquant\n";
}

// 5. Test de la configuration Inertia
echo "\n5. Test de la configuration Inertia...\n";
$appFile = 'resources/js/app.jsx';
if (file_exists($appFile)) {
    $content = file_get_contents($appFile);
    
    $checks = [
        'createInertiaApp' => 'Configuration Inertia',
        '@inertiajs/react' => 'Import Inertia React',
        'resolvePageComponent' => 'Résolution des pages'
    ];
    
    foreach ($checks as $check => $description) {
        if (strpos($content, $check) !== false) {
            echo "  ✓ $description\n";
        } else {
            echo "  ✗ $description manquante\n";
        }
    }
} else {
    echo "  ✗ app.jsx manquant\n";
}

// 6. Test des pages React
echo "\n6. Test des pages React...\n";
$pages = [
    'resources/js/Pages/Test.jsx',
    'resources/js/Pages/Auth/LoginSimple.jsx',
    'resources/js/Pages/Auth/Login.jsx'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        $content = file_get_contents($page);
        if (strpos($content, 'export default') !== false) {
            echo "  ✓ $page valide\n";
        } else {
            echo "  ✗ $page invalide (pas d'export default)\n";
        }
    } else {
        echo "  ✗ $page manquant\n";
    }
}

// 7. Test de la base de données
echo "\n7. Test de la base de données...\n";
try {
    $users = \App\Models\User::all();
    echo "  ✓ Connexion DB OK - " . $users->count() . " utilisateurs\n";
    
    $admins = \App\Models\User::where('role', 'admin')->get();
    if ($admins->count() > 0) {
        echo "  ✓ " . $admins->count() . " administrateur(s) trouvé(s)\n";
    } else {
        echo "  ⚠ Aucun administrateur trouvé - Exécutez: php create-admin.php\n";
    }
} catch (Exception $e) {
    echo "  ✗ Erreur DB: " . $e->getMessage() . "\n";
}

// 8. Test des logs
echo "\n8. Test des logs...\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "  ✓ Log file existe (taille: " . round($logSize / 1024, 2) . " KB)\n";
    
    if ($logSize > 0) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -5);
        
        echo "  Dernières lignes:\n";
        foreach ($recentLines as $line) {
            if (trim($line) !== '') {
                echo "    " . substr($line, 0, 100) . "...\n";
            }
        }
    }
} else {
    echo "  ⚠ Aucun fichier de log trouvé\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ Tests terminés\n";
echo "\nProchaines étapes:\n";
echo "1. Testez /test dans votre navigateur\n";
echo "2. Testez /login-simple dans votre navigateur\n";
echo "3. Vérifiez la console du navigateur (F12)\n";
echo "4. Si les pages sont blanches, vérifiez:\n";
echo "   - Les erreurs JavaScript dans la console\n";
echo "   - Les erreurs réseau dans l'onglet Network\n";
echo "   - Les permissions des fichiers\n";
echo "\nSi le problème persiste, essayez:\n";
echo "npm run build\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n"; 