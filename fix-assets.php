<?php

echo "=== RÉPARATION DES ASSETS ===\n\n";

// 1. Nettoyer le cache
echo "1. Nettoyage du cache...\n";
system('php artisan config:clear');
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan route:clear');

echo "✓ Cache nettoyé\n\n";

// 2. Supprimer les anciens builds
echo "2. Suppression des anciens builds...\n";
if (is_dir('public/build')) {
    system('rm -rf public/build');
    echo "✓ Ancien build supprimé\n";
} else {
    echo "✓ Pas d'ancien build à supprimer\n";
}

echo "\n";

// 3. Vérifier les dépendances
echo "3. Vérification des dépendances...\n";
if (file_exists('package.json')) {
    echo "✓ package.json trouvé\n";
    
    // Vérifier si node_modules existe
    if (!is_dir('node_modules')) {
        echo "⚠ node_modules manquant, installation...\n";
        system('npm install');
    } else {
        echo "✓ node_modules existe\n";
    }
} else {
    echo "✗ package.json manquant\n";
    exit(1);
}

echo "\n";

// 4. Reconstruire les assets
echo "4. Reconstruction des assets...\n";
echo "Exécution de npm run build...\n";
$output = shell_exec('npm run build 2>&1');
echo "Sortie:\n$output\n";

// 5. Vérifier le résultat
echo "5. Vérification du résultat...\n";
if (file_exists('public/build/manifest.json')) {
    echo "✓ manifest.json créé\n";
    
    $manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
    if ($manifest) {
        echo "✓ manifest.json valide\n";
        echo "  Nombre d'entrées: " . count($manifest) . "\n";
    } else {
        echo "✗ manifest.json invalide\n";
    }
} else {
    echo "✗ manifest.json non créé\n";
}

if (is_dir('public/build/assets')) {
    $assets = scandir('public/build/assets');
    $jsFiles = array_filter($assets, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'js';
    });
    $cssFiles = array_filter($assets, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'css';
    });
    
    echo "✓ Assets créés\n";
    echo "  Fichiers JS: " . count($jsFiles) . "\n";
    echo "  Fichiers CSS: " . count($cssFiles) . "\n";
} else {
    echo "✗ Dossier assets non créé\n";
}

echo "\n";

// 6. Corriger les permissions
echo "6. Correction des permissions...\n";
system('chmod -R 755 public/build');
system('chmod -R 755 storage');
echo "✓ Permissions corrigées\n";

echo "\n";

// 7. Test final
echo "7. Test final...\n";
if (file_exists('public/build/manifest.json')) {
    $manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
    
    // Chercher les entrées principales
    $mainEntries = ['resources/js/app.jsx'];
    foreach ($mainEntries as $entry) {
        if (isset($manifest[$entry])) {
            $file = 'public/build/' . $manifest[$entry]['file'];
            if (file_exists($file)) {
                echo "✓ $entry -> {$manifest[$entry]['file']}\n";
            } else {
                echo "✗ $entry -> fichier manquant\n";
            }
        } else {
            echo "⚠ $entry non trouvé dans le manifest\n";
        }
    }
}

echo "\n=== RÉPARATION TERMINÉE ===\n";
echo "Si les problèmes persistent:\n";
echo "1. Vérifiez les logs du serveur web\n";
echo "2. Vérifiez la configuration du serveur web\n";
echo "3. Testez avec: php diagnostic-assets.php\n";

?> 