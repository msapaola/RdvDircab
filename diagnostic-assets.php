<?php

echo "=== DIAGNOSTIC DES ASSETS ===\n\n";

// 1. Vérifier la configuration Vite
echo "1. Configuration Vite...\n";
if (file_exists('vite.config.js')) {
    echo "✓ vite.config.js existe\n";
    $viteConfig = file_get_contents('vite.config.js');
    
    if (strpos($viteConfig, 'outDir: \'public/build\'') !== false) {
        echo "✓ outDir configuré vers public/build\n";
    } else {
        echo "⚠ outDir non configuré vers public/build\n";
    }
    
    if (strpos($viteConfig, 'base: \'/build/\'') !== false) {
        echo "✓ base configuré vers /build/\n";
    } else {
        echo "⚠ base non configuré vers /build/\n";
    }
} else {
    echo "✗ vite.config.js manquant\n";
}

echo "\n";

// 2. Vérifier les fichiers de build
echo "2. Fichiers de build...\n";
$buildDir = 'public/build';
$manifestFile = $buildDir . '/manifest.json';

if (is_dir($buildDir)) {
    echo "✓ Dossier public/build existe\n";
    
    if (file_exists($manifestFile)) {
        echo "✓ manifest.json existe\n";
        
        $manifest = json_decode(file_get_contents($manifestFile), true);
        if ($manifest) {
            echo "✓ manifest.json valide (JSON)\n";
            echo "  Nombre d'entrées: " . count($manifest) . "\n";
        } else {
            echo "✗ manifest.json invalide\n";
        }
    } else {
        echo "✗ manifest.json manquant\n";
    }
    
    $assetsDir = $buildDir . '/assets';
    if (is_dir($assetsDir)) {
        $assets = scandir($assetsDir);
        $jsFiles = array_filter($assets, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'js';
        });
        $cssFiles = array_filter($assets, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'css';
        });
        
        echo "✓ Dossier assets existe\n";
        echo "  Fichiers JS: " . count($jsFiles) . "\n";
        echo "  Fichiers CSS: " . count($cssFiles) . "\n";
    } else {
        echo "✗ Dossier assets manquant\n";
    }
} else {
    echo "✗ Dossier public/build manquant\n";
}

echo "\n";

// 3. Vérifier le template Blade
echo "3. Template Blade...\n";
$bladeFile = 'resources/views/app.blade.php';
if (file_exists($bladeFile)) {
    echo "✓ app.blade.php existe\n";
    
    $bladeContent = file_get_contents($bladeFile);
    
    if (strpos($bladeContent, '@viteReactRefresh') !== false) {
        echo "✓ @viteReactRefresh présent\n";
    } else {
        echo "⚠ @viteReactRefresh manquant\n";
    }
    
    if (strpos($bladeContent, '@vite([') !== false) {
        echo "✓ @vite directive présente\n";
    } else {
        echo "⚠ @vite directive manquante\n";
    }
    
    if (strpos($bladeContent, '@inertiaHead') !== false) {
        echo "✓ @inertiaHead présent\n";
    } else {
        echo "⚠ @inertiaHead manquant\n";
    }
} else {
    echo "✗ app.blade.php manquant\n";
}

echo "\n";

// 4. Vérifier les fichiers source
echo "4. Fichiers source...\n";
$sourceFiles = [
    'resources/js/app.jsx',
    'resources/js/bootstrap.js',
    'resources/css/app.css'
];

foreach ($sourceFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file existe\n";
    } else {
        echo "✗ $file manquant\n";
    }
}

echo "\n";

// 5. Vérifier les dépendances
echo "5. Dépendances...\n";
$packageJson = 'package.json';
if (file_exists($packageJson)) {
    echo "✓ package.json existe\n";
    
    $package = json_decode(file_get_contents($packageJson), true);
    if ($package) {
        $deps = $package['dependencies'] ?? [];
        $devDeps = $package['devDependencies'] ?? [];
        
        $requiredDeps = ['@inertiajs/react', 'react', 'react-dom'];
        $requiredDevDeps = ['@vitejs/plugin-react', 'vite', 'laravel-vite-plugin'];
        
        foreach ($requiredDeps as $dep) {
            if (isset($deps[$dep])) {
                echo "✓ $dep installé (v{$deps[$dep]})\n";
            } else {
                echo "✗ $dep manquant\n";
            }
        }
        
        foreach ($requiredDevDeps as $dep) {
            if (isset($devDeps[$dep])) {
                echo "✓ $dep installé (v{$devDeps[$dep]})\n";
            } else {
                echo "✗ $dep manquant\n";
            }
        }
    }
} else {
    echo "✗ package.json manquant\n";
}

echo "\n";

// 6. Test de build
echo "6. Test de build...\n";
echo "Exécution de npm run build...\n";
$output = shell_exec('npm run build 2>&1');
echo "Sortie:\n$output\n";

// 7. Vérifier les permissions
echo "7. Permissions...\n";
$dirs = ['public/build', 'public/build/assets', 'storage'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "  $dir: $perms\n";
        
        if ($perms >= '0755') {
            echo "    ✓ Permissions OK\n";
        } else {
            echo "    ⚠ Permissions faibles\n";
        }
    }
}

echo "\n";

// 8. Test d'accès aux assets
echo "8. Test d'accès aux assets...\n";
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    
    // Chercher le fichier app principal
    $appEntry = null;
    foreach ($manifest as $key => $entry) {
        if (strpos($key, 'app.jsx') !== false || (isset($entry['file']) && strpos($entry['file'], 'app-') !== false)) {
            $appEntry = $entry;
            break;
        }
    }
    
    if ($appEntry && isset($appEntry['file'])) {
        $appFile = 'public/build/' . $appEntry['file'];
        if (file_exists($appFile)) {
            echo "✓ Fichier app principal trouvé: {$appEntry['file']}\n";
            echo "  Taille: " . number_format(filesize($appFile) / 1024, 2) . " KB\n";
        } else {
            echo "✗ Fichier app principal manquant: {$appEntry['file']}\n";
        }
    } else {
        echo "⚠ Entrée app non trouvée dans le manifest\n";
    }
}

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";
echo "Si des erreurs sont détectées:\n";
echo "1. Exécutez: npm install\n";
echo "2. Exécutez: npm run build\n";
echo "3. Vérifiez les permissions: chmod -R 755 public/build\n";
echo "4. Vérifiez les logs du serveur web\n"; 