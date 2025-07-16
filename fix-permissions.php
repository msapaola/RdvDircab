<?php

// Script pour diagnostiquer et corriger les permissions
echo "=== Diagnostic des Permissions ===\n\n";

$publicHtml = __DIR__;
$buildPath = $publicHtml . '/public/build';

echo "1. Vérification des permissions actuelles...\n";
echo "   📁 Public HTML: $publicHtml\n";
echo "   📁 Build path: $buildPath\n\n";

// Vérifier les permissions du dossier public_html
$publicHtmlPerms = substr(sprintf('%o', fileperms($publicHtml)), -4);
echo "   🔐 Permissions public_html: $publicHtmlPerms\n";

// Vérifier les permissions du dossier public
$publicPath = $publicHtml . '/public';
if (is_dir($publicPath)) {
    $publicPerms = substr(sprintf('%o', fileperms($publicPath)), -4);
    echo "   🔐 Permissions public: $publicPerms\n";
} else {
    echo "   ❌ Dossier public manquant\n";
}

// Vérifier les permissions du dossier build
if (is_dir($buildPath)) {
    $buildPerms = substr(sprintf('%o', fileperms($buildPath)), -4);
    echo "   🔐 Permissions build: $buildPerms\n";
    
    // Vérifier les permissions du dossier assets
    $assetsPath = $buildPath . '/assets';
    if (is_dir($assetsPath)) {
        $assetsPerms = substr(sprintf('%o', fileperms($assetsPath)), -4);
        echo "   🔐 Permissions assets: $assetsPerms\n";
        
        // Vérifier quelques fichiers
        $files = ['app-8cgd_IZT.css', 'app-pg7X1LG8.js', 'manifest.json'];
        foreach ($files as $file) {
            $filePath = $buildPath . '/' . $file;
            if (file_exists($filePath)) {
                $filePerms = substr(sprintf('%o', fileperms($filePath)), -4);
                echo "   🔐 Permissions $file: $filePerms\n";
            }
        }
    }
} else {
    echo "   ❌ Dossier build manquant\n";
}

echo "\n2. Correction des permissions...\n";

// Corriger les permissions des dossiers (755)
$directories = [
    $publicHtml,
    $publicPath,
    $buildPath,
    $buildPath . '/assets'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (chmod($dir, 0755)) {
            echo "   ✅ Permissions 755 pour: " . basename($dir) . "\n";
        } else {
            echo "   ❌ Impossible de changer les permissions pour: " . basename($dir) . "\n";
        }
    }
}

// Corriger les permissions des fichiers (644)
if (is_dir($buildPath)) {
    $files = glob($buildPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            if (chmod($file, 0644)) {
                echo "   ✅ Permissions 644 pour: " . basename($file) . "\n";
            }
        }
    }
    
    // Corriger les permissions des fichiers dans assets
    $assetsPath = $buildPath . '/assets';
    if (is_dir($assetsPath)) {
        $assetFiles = glob($assetsPath . '/*');
        foreach ($assetFiles as $file) {
            if (is_file($file)) {
                if (chmod($file, 0644)) {
                    echo "   ✅ Permissions 644 pour assets/" . basename($file) . "\n";
                }
            }
        }
    }
}

echo "\n3. Test d'accès web...\n";

// Simuler un accès web
$testUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/public/build/assets/app-8cgd_IZT.css';
echo "   🔗 Test URL: $testUrl\n";

// Vérifier si le fichier est accessible via HTTP
$headers = get_headers($testUrl);
if ($headers && strpos($headers[0], '200') !== false) {
    echo "   ✅ Fichier accessible via HTTP\n";
} else {
    echo "   ❌ Fichier non accessible via HTTP\n";
    echo "   📋 Headers: " . implode(', ', $headers) . "\n";
}

echo "\n4. Vérification du fichier .htaccess...\n";
$htaccessPath = $publicHtml . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "   ✅ Fichier .htaccess trouvé\n";
    $htaccessContent = file_get_contents($htaccessPath);
    if (strpos($htaccessContent, 'RewriteEngine') !== false) {
        echo "   ⚠️  Mod_rewrite activé - peut interférer avec l'accès direct\n";
    }
} else {
    echo "   ℹ️  Aucun fichier .htaccess trouvé\n";
}

echo "\n=== Diagnostic terminé ===\n";
echo "💡 Suggestions:\n";
echo "   1. Si les permissions sont correctes mais l'accès échoue, c'est un problème de configuration serveur\n";
echo "   2. Contactez votre hébergeur pour vérifier les restrictions d'accès\n";
echo "   3. Essayez d'accéder directement à: https://votre-domaine.com/public/build/assets/app-8cgd_IZT.css\n"; 