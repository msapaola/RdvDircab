<?php

echo "=== TEST FINAL CORRECTION ===\n\n";

// 1. Vérifier que le layout a été corrigé
echo "1. Vérification du layout...\n";
$layoutFile = 'resources/views/app.blade.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);
    
    if (strpos($content, '@vite([\'resources/js/app.jsx\'])') !== false) {
        echo "✓ Layout corrigé - Vite configuré correctement\n";
    } else {
        echo "✗ Layout non corrigé\n";
    }
} else {
    echo "✗ Layout manquant\n";
}

// 2. Vérifier le manifest
echo "\n2. Vérification du manifest...\n";
$manifestFile = 'public/build/manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ Manifest valide\n";
        
        // Vérifier app.jsx
        if (isset($manifest['resources/js/app.jsx'])) {
            echo "✓ app.jsx dans le manifest\n";
        } else {
            echo "✗ app.jsx manquant dans le manifest\n";
        }
        
        // Vérifier pages-imports.js
        if (isset($manifest['resources/js/pages-imports.js'])) {
            echo "✓ pages-imports.js dans le manifest\n";
        } else {
            echo "⚠ pages-imports.js manquant (optionnel)\n";
        }
    } else {
        echo "✗ Manifest invalide\n";
    }
} else {
    echo "✗ Manifest manquant\n";
}

// 3. Test des routes
echo "\n3. Test des routes...\n";
$routes = [
    '/test',
    '/login-simple',
    '/login'
];

try {
    $app = require_once 'bootstrap/app.php';
    
    foreach ($routes as $route) {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('GET');
        $request->setUri($route);
        
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        
        $status = $response->getStatusCode();
        echo "  $route: Status $status\n";
        
        if ($status === 200) {
            echo "    ✓ Page accessible\n";
        } elseif ($status === 302) {
            echo "    ⚠ Redirection (normal)\n";
        } else {
            echo "    ✗ Problème\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test des routes: " . $e->getMessage() . "\n";
}

// 4. Vérifier les fichiers de build
echo "\n4. Vérification des fichiers de build...\n";
$buildDir = 'public/build';
if (is_dir($buildDir)) {
    $jsFiles = glob($buildDir . '/assets/*.js');
    $cssFiles = glob($buildDir . '/assets/*.css');
    
    if (!empty($jsFiles)) {
        echo "✓ Fichiers JS trouvés: " . count($jsFiles) . "\n";
        foreach ($jsFiles as $file) {
            echo "  - " . basename($file) . "\n";
        }
    } else {
        echo "✗ Aucun fichier JS trouvé\n";
    }
    
    if (!empty($cssFiles)) {
        echo "✓ Fichiers CSS trouvés: " . count($cssFiles) . "\n";
        foreach ($cssFiles as $file) {
            echo "  - " . basename($file) . "\n";
        }
    } else {
        echo "✗ Aucun fichier CSS trouvé\n";
    }
} else {
    echo "✗ Dossier build manquant\n";
}

// 5. Test de la base de données
echo "\n5. Test de la base de données...\n";
try {
    $users = \App\Models\User::all();
    echo "✓ Connexion DB OK - " . $users->count() . " utilisateurs\n";
    
    $admins = \App\Models\User::where('role', 'admin')->get();
    if ($admins->count() > 0) {
        echo "✓ " . $admins->count() . " administrateur(s) trouvé(s)\n";
    } else {
        echo "⚠ Aucun administrateur trouvé\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur DB: " . $e->getMessage() . "\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "✅ Tests terminés\n";
echo "\nMaintenant testez dans votre navigateur:\n";
echo "1. https://green-wolverine-495039.hostingersite.com/test\n";
echo "2. https://green-wolverine-495039.hostingersite.com/login-simple\n";
echo "3. https://green-wolverine-495039.hostingersite.com/login\n";
echo "\nSi les pages fonctionnent:\n";
echo "1. Créez l'utilisateur admin: php create-admin.php\n";
echo "2. Connectez-vous avec admin@gouvernorat-kinshasa.cd\n";
echo "3. Accédez à /admin/dashboard\n";
echo "\nSi les pages sont encore blanches:\n";
echo "1. Vérifiez la console du navigateur (F12)\n";
echo "2. Vérifiez les logs: tail -f storage/logs/laravel.log\n";
echo "3. Vérifiez que les assets se chargent dans l'onglet Network\n"; 