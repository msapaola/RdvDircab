<?php

echo "=== TEST D'ACCÈS AUX ASSETS ===\n\n";

// 1. Test de base
echo "1. Test de base...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app chargée\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test du manifest
echo "\n2. Test du manifest...\n";
$manifestFile = 'public/build/manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ manifest.json accessible et valide\n";
        
        // Chercher l'entrée principale
        $appEntry = null;
        foreach ($manifest as $key => $entry) {
            if (strpos($key, 'app.jsx') !== false) {
                $appEntry = $entry;
                break;
            }
        }
        
        if ($appEntry) {
            echo "✓ Entrée app trouvée: {$appEntry['file']}\n";
            
            $appFile = 'public/build/' . $appEntry['file'];
            if (file_exists($appFile)) {
                echo "✓ Fichier app accessible\n";
                echo "  Taille: " . number_format(filesize($appFile) / 1024, 2) . " KB\n";
            } else {
                echo "✗ Fichier app manquant\n";
            }
        } else {
            echo "⚠ Entrée app non trouvée\n";
        }
    } else {
        echo "✗ manifest.json invalide\n";
    }
} else {
    echo "✗ manifest.json manquant\n";
}

// 3. Test des routes
echo "\n3. Test des routes...\n";
$routes = [
    '/',
    '/login',
    '/admin/dashboard'
];

foreach ($routes as $route) {
    try {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('GET');
        $request->setUri($route);
        
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        
        $status = $response->getStatusCode();
        if ($status === 200) {
            echo "✓ $route - OK\n";
        } elseif ($status === 302) {
            echo "✓ $route - Redirection (normal)\n";
        } else {
            echo "⚠ $route - Status: $status\n";
        }
    } catch (Exception $e) {
        echo "✗ $route - Erreur: " . $e->getMessage() . "\n";
    }
}

// 4. Test de la page d'accueil
echo "\n4. Test de la page d'accueil...\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->setMethod('GET');
    $request->setUri('/');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    $content = $response->getContent();
    
    // Vérifier si les assets sont inclus
    if (strpos($content, '/build/') !== false) {
        echo "✓ Assets référencés dans la page\n";
    } else {
        echo "⚠ Assets non référencés dans la page\n";
    }
    
    if (strpos($content, 'manifest.json') !== false) {
        echo "✓ manifest.json référencé\n";
    } else {
        echo "⚠ manifest.json non référencé\n";
    }
    
    // Afficher les premières lignes pour debug
    $lines = explode("\n", $content);
    $firstLines = array_slice($lines, 0, 10);
    echo "Premières lignes de la page:\n";
    foreach ($firstLines as $line) {
        if (trim($line) !== '') {
            echo "  " . trim($line) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur lors du test de la page: " . $e->getMessage() . "\n";
}

echo "\n=== TEST TERMINÉ ===\n"; 