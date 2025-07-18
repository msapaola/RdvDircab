<?php

echo "=== DIAGNOSTIC PAGE LOGIN ===\n\n";

// 1. Test de base Laravel
echo "1. Test de base Laravel...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app chargée\n";
} catch (Exception $e) {
    echo "✗ Erreur Laravel: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test de la route login
echo "\n2. Test de la route login...\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->setMethod('GET');
    $request->setUri('/login');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Route login accessible\n";
    } else {
        echo "✗ Route login retourne status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur route: " . $e->getMessage() . "\n";
}

// 3. Test du contrôleur
echo "\n3. Test du contrôleur AuthenticatedSessionController...\n";
try {
    $controller = new \App\Http\Controllers\Auth\AuthenticatedSessionController();
    echo "✓ Contrôleur chargé\n";
    
    // Test de la méthode create
    $response = $controller->create();
    echo "✓ Méthode create() exécutée\n";
    echo "Type de réponse: " . get_class($response) . "\n";
} catch (Exception $e) {
    echo "✗ Erreur contrôleur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
}

// 4. Test d'Inertia
echo "\n4. Test d'Inertia...\n";
try {
    $inertia = new \Inertia\Inertia();
    echo "✓ Inertia chargé\n";
} catch (Exception $e) {
    echo "✗ Erreur Inertia: " . $e->getMessage() . "\n";
}

// 5. Test des assets
echo "\n5. Test des assets...\n";
$assetFiles = [
    'public/build/manifest.json',
    'resources/js/app.jsx',
    'resources/js/Pages/Auth/Login.jsx'
];

foreach ($assetFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file existe\n";
    } else {
        echo "✗ $file manquant\n";
    }
}

// 6. Test de la page Login.jsx
echo "\n6. Test de la page Login.jsx...\n";
$loginFile = 'resources/js/Pages/Auth/Login.jsx';
if (file_exists($loginFile)) {
    $content = file_get_contents($loginFile);
    if (strpos($content, 'export default') !== false) {
        echo "✓ Login.jsx semble valide\n";
    } else {
        echo "⚠ Login.jsx pourrait avoir des problèmes\n";
    }
} else {
    echo "✗ Login.jsx n'existe pas\n";
}

// 7. Test des logs d'erreur
echo "\n7. Test des logs d'erreur...\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -10);
    
    echo "Dernières lignes du log:\n";
    foreach ($recentLines as $line) {
        if (trim($line) !== '') {
            echo "  " . $line . "\n";
        }
    }
} else {
    echo "Aucun fichier de log trouvé\n";
}

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";
echo "Si la page est blanche, vérifiez:\n";
echo "1. Les logs d'erreur ci-dessus\n";
echo "2. La console du navigateur (F12)\n";
echo "3. Les assets sont-ils compilés?\n";
echo "4. Inertia.js est-il correctement configuré?\n"; 