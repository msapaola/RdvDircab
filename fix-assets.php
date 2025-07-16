<?php

// Script pour diagnostiquer et corriger les problèmes d'assets
echo "=== Diagnostic des Assets ===\n\n";

// 1. Vérifier si le dossier build existe
$buildPath = __DIR__ . '/public/build';
echo "1. Vérification du dossier build...\n";
if (is_dir($buildPath)) {
    echo "   ✅ Dossier build trouvé: $buildPath\n";
} else {
    echo "   ❌ Dossier build manquant: $buildPath\n";
    exit(1);
}

// 2. Vérifier le manifest
$manifestPath = $buildPath . '/manifest.json';
echo "\n2. Vérification du manifest...\n";
if (file_exists($manifestPath)) {
    echo "   ✅ Manifest trouvé: $manifestPath\n";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if ($manifest) {
        echo "   ✅ Manifest valide (JSON)\n";
    } else {
        echo "   ❌ Manifest invalide (JSON corrompu)\n";
    }
} else {
    echo "   ❌ Manifest manquant: $manifestPath\n";
}

// 3. Vérifier le dossier assets
$assetsPath = $buildPath . '/assets';
echo "\n3. Vérification du dossier assets...\n";
if (is_dir($assetsPath)) {
    echo "   ✅ Dossier assets trouvé: $assetsPath\n";
    $files = scandir($assetsPath);
    $assetFiles = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..';
    });
    echo "   📁 Nombre de fichiers assets: " . count($assetFiles) . "\n";
    
    // Lister quelques fichiers
    $sampleFiles = array_slice($assetFiles, 0, 5);
    foreach ($sampleFiles as $file) {
        echo "      - $file\n";
    }
    if (count($assetFiles) > 5) {
        echo "      ... et " . (count($assetFiles) - 5) . " autres fichiers\n";
    }
} else {
    echo "   ❌ Dossier assets manquant: $assetsPath\n";
}

// 4. Vérifier les permissions
echo "\n4. Vérification des permissions...\n";
if (is_readable($buildPath)) {
    echo "   ✅ Dossier build lisible\n";
} else {
    echo "   ❌ Dossier build non lisible\n";
}

if (is_readable($assetsPath)) {
    echo "   ✅ Dossier assets lisible\n";
} else {
    echo "   ❌ Dossier assets non lisible\n";
}

// 5. Tester l'accès web
echo "\n5. Test d'accès web...\n";
$testFile = $assetsPath . '/app-8cgd_IZT.css';
if (file_exists($testFile)) {
    echo "   ✅ Fichier de test trouvé: app-8cgd_IZT.css\n";
    
    // Vérifier la taille
    $size = filesize($testFile);
    echo "   📏 Taille: " . number_format($size) . " bytes\n";
} else {
    echo "   ❌ Fichier de test manquant: app-8cgd_IZT.css\n";
}

// 6. Vérifier la structure complète
echo "\n6. Structure du dossier build:\n";
function listDirectory($path, $indent = '') {
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $path . '/' . $item;
        if (is_dir($fullPath)) {
            echo "$indent📁 $item/\n";
            listDirectory($fullPath, $indent . '  ');
        } else {
            $size = filesize($fullPath);
            echo "$indent📄 $item (" . number_format($size) . " bytes)\n";
        }
    }
}

listDirectory($buildPath);

echo "\n=== Fin du diagnostic ===\n";

// 7. Suggestions de correction
echo "\n=== Suggestions de correction ===\n";

if (!file_exists($manifestPath)) {
    echo "1. Copier le manifest depuis .vite/manifest.json:\n";
    echo "   cp public/build/.vite/manifest.json public/build/manifest.json\n\n";
}

if (!is_dir($assetsPath)) {
    echo "2. Recompiler les assets:\n";
    echo "   npm run build\n\n";
}

echo "3. Vérifier que tous les fichiers sont transférés sur le serveur\n";
echo "4. Vérifier les permissions (755 pour les dossiers, 644 pour les fichiers)\n";
echo "5. Vérifier que le serveur web peut accéder au dossier public/build\n";

?> 