<?php

echo "=== RECOMPILATION DES ASSETS ===\n\n";

// 1. Nettoyer les anciens assets
echo "1. Nettoyage des anciens assets...\n";
$buildDir = 'public/build';
if (is_dir($buildDir)) {
    $files = glob($buildDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "  Supprimé: " . basename($file) . "\n";
        }
    }
    echo "✓ Anciens assets supprimés\n";
} else {
    echo "✓ Dossier build vide ou inexistant\n";
}

// 2. Vérifier les dépendances npm
echo "\n2. Vérification des dépendances...\n";
if (file_exists('package.json')) {
    echo "✓ package.json trouvé\n";
    
    // Vérifier node_modules
    if (is_dir('node_modules')) {
        echo "✓ node_modules existe\n";
    } else {
        echo "⚠ node_modules manquant - Exécutez: npm install\n";
    }
} else {
    echo "✗ package.json manquant\n";
    exit(1);
}

// 3. Installer les dépendances si nécessaire
echo "\n3. Installation des dépendances...\n";
echo "Exécution de npm install...\n";
system('npm install 2>&1');

// 4. Recompiler les assets
echo "\n4. Recompilation des assets...\n";
echo "Exécution de npm run build...\n";
$output = shell_exec('npm run build 2>&1');
echo $output;

// 5. Vérifier le résultat
echo "\n5. Vérification du résultat...\n";
if (file_exists('public/build/manifest.json')) {
    echo "✓ Manifest créé\n";
    
    $manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
    if ($manifest) {
        echo "✓ Manifest valide\n";
        echo "Entrées: " . count($manifest) . "\n";
        
        foreach ($manifest as $key => $value) {
            echo "  - $key\n";
        }
    } else {
        echo "✗ Manifest invalide\n";
    }
} else {
    echo "✗ Manifest non créé\n";
}

// 6. Vérifier les fichiers CSS et JS
echo "\n6. Vérification des fichiers CSS et JS...\n";
$jsFiles = glob('public/build/assets/*.js');
$cssFiles = glob('public/build/assets/*.css');

if (!empty($jsFiles)) {
    echo "✓ Fichiers JS créés: " . count($jsFiles) . "\n";
    foreach ($jsFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
} else {
    echo "✗ Aucun fichier JS créé\n";
}

if (!empty($cssFiles)) {
    echo "✓ Fichiers CSS créés: " . count($cssFiles) . "\n";
    foreach ($cssFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
} else {
    echo "✗ Aucun fichier CSS créé\n";
}

// 7. Nettoyer les caches Laravel
echo "\n7. Nettoyage des caches Laravel...\n";
system('php artisan config:clear 2>&1');
system('php artisan cache:clear 2>&1');
system('php artisan view:clear 2>&1');
system('php artisan route:clear 2>&1');
echo "✓ Caches nettoyés\n";

// 8. Test final
echo "\n8. Test final...\n";
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

echo "\n=== RECOMPILATION TERMINÉE ===\n";
echo "Maintenant testez:\n";
echo "1. /test - Page de test\n";
echo "2. /login-simple - Login simplifié\n";
echo "3. /login - Login original\n";
echo "\nSi les pages sont encore blanches:\n";
echo "1. Vérifiez la console du navigateur (F12)\n";
echo "2. Vérifiez les permissions des fichiers\n";
echo "3. Vérifiez la configuration du serveur web\n"; 