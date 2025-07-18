<?php

echo "=== CORRECTION VITE MANIFEST ===\n\n";

// 1. Vérifier la configuration Vite
echo "1. Vérification de la configuration Vite...\n";
$viteConfig = 'vite.config.js';
if (file_exists($viteConfig)) {
    $content = file_get_contents($viteConfig);
    echo "✓ vite.config.js trouvé\n";
    
    if (strpos($content, 'resources/js/Pages') !== false) {
        echo "✓ Configuration Pages détectée\n";
    } else {
        echo "⚠ Configuration Pages manquante\n";
    }
} else {
    echo "✗ vite.config.js manquant\n";
}

// 2. Vérifier le fichier app.jsx
echo "\n2. Vérification de app.jsx...\n";
$appFile = 'resources/js/app.jsx';
if (file_exists($appFile)) {
    $content = file_get_contents($appFile);
    
    if (strpos($content, 'createInertiaApp') !== false) {
        echo "✓ Configuration Inertia détectée\n";
    } else {
        echo "✗ Configuration Inertia manquante\n";
    }
    
    if (strpos($content, 'resolvePageComponent') !== false) {
        echo "✓ Résolution des pages configurée\n";
    } else {
        echo "✗ Résolution des pages manquante\n";
    }
} else {
    echo "✗ app.jsx manquant\n";
}

// 3. Vérifier les pages existantes
echo "\n3. Vérification des pages...\n";
$pagesDir = 'resources/js/Pages';
if (is_dir($pagesDir)) {
    $pages = glob($pagesDir . '/**/*.jsx');
    echo "✓ Pages trouvées: " . count($pages) . "\n";
    
    foreach ($pages as $page) {
        $relativePath = str_replace('resources/js/', '', $page);
        echo "  - $relativePath\n";
    }
} else {
    echo "✗ Dossier Pages manquant\n";
}

// 4. Vérifier le manifest actuel
echo "\n4. Vérification du manifest actuel...\n";
$manifestFile = 'public/build/manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ Manifest valide\n";
        echo "Entrées dans le manifest:\n";
        foreach ($manifest as $key => $value) {
            echo "  - $key\n";
        }
        
        // Vérifier si Test.jsx est dans le manifest
        if (isset($manifest['resources/js/Pages/Test.jsx'])) {
            echo "✓ Test.jsx trouvé dans le manifest\n";
        } else {
            echo "✗ Test.jsx manquant dans le manifest\n";
        }
    } else {
        echo "✗ Manifest invalide\n";
    }
} else {
    echo "✗ Manifest manquant\n";
}

// 5. Nettoyer et reconstruire
echo "\n5. Nettoyage et reconstruction...\n";

// Supprimer le manifest
if (file_exists($manifestFile)) {
    unlink($manifestFile);
    echo "✓ Ancien manifest supprimé\n";
}

// Supprimer les assets
$buildDir = 'public/build';
if (is_dir($buildDir)) {
    $files = glob($buildDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ Anciens assets supprimés\n";
}

// 6. Reconstruire avec Vite
echo "\n6. Reconstruction avec Vite...\n";
echo "Exécution de npm run build...\n";
$output = shell_exec('npm run build 2>&1');
echo $output;

// 7. Vérifier le nouveau manifest
echo "\n7. Vérification du nouveau manifest...\n";
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ Nouveau manifest créé\n";
        echo "Entrées:\n";
        foreach ($manifest as $key => $value) {
            echo "  - $key\n";
        }
        
        // Vérifier les pages importantes
        $importantPages = [
            'resources/js/Pages/Test.jsx',
            'resources/js/Pages/Auth/Login.jsx',
            'resources/js/Pages/Auth/LoginSimple.jsx'
        ];
        
        foreach ($importantPages as $page) {
            if (isset($manifest[$page])) {
                echo "✓ $page dans le manifest\n";
            } else {
                echo "✗ $page manquant dans le manifest\n";
            }
        }
    } else {
        echo "✗ Nouveau manifest invalide\n";
    }
} else {
    echo "✗ Nouveau manifest non créé\n";
}

// 8. Nettoyer les caches Laravel
echo "\n8. Nettoyage des caches Laravel...\n";
system('php artisan config:clear 2>&1');
system('php artisan cache:clear 2>&1');
system('php artisan view:clear 2>&1');
system('php artisan route:clear 2>&1');
echo "✓ Caches nettoyés\n";

// 9. Test final
echo "\n9. Test final...\n";
try {
    $app = require_once 'bootstrap/app.php';
    
    $request = new \Illuminate\Http\Request();
    $request->setMethod('GET');
    $request->setUri('/test');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Page de test accessible\n";
    } else {
        echo "⚠ Page de test retourne status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test: " . $e->getMessage() . "\n";
}

echo "\n=== CORRECTION TERMINÉE ===\n";
echo "Maintenant testez:\n";
echo "1. /test - Page de test\n";
echo "2. /login-simple - Login simplifié\n";
echo "3. /login - Login original\n";
echo "\nSi le problème persiste:\n";
echo "1. Vérifiez que Vite est correctement configuré\n";
echo "2. Vérifiez que tous les fichiers JSX sont valides\n";
echo "3. Vérifiez les logs d'erreur\n"; 