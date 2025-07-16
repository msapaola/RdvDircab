<?php

// Script pour préparer le transfert des assets
echo "=== Préparation du transfert des assets ===\n\n";

$buildPath = __DIR__ . '/public/build';
$outputPath = __DIR__ . '/assets-for-transfer';

// Créer le dossier de sortie
if (!is_dir($outputPath)) {
    mkdir($outputPath, 0755, true);
    echo "✅ Dossier de sortie créé: $outputPath\n";
}

// Copier le dossier build
function copyDirectory($source, $destination) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $files = scandir($source);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $sourcePath = $source . '/' . $file;
        $destPath = $destination . '/' . $file;
        
        if (is_dir($sourcePath)) {
            copyDirectory($sourcePath, $destPath);
        } else {
            copy($sourcePath, $destPath);
            chmod($destPath, 0644);
        }
    }
}

echo "📁 Copie du dossier build...\n";
copyDirectory($buildPath, $outputPath . '/build');

// Créer un fichier README avec les instructions
$readme = "INSTRUCTIONS DE TRANSFERT DES ASSETS\n";
$readme .= "=====================================\n\n";
$readme .= "1. Transférez le dossier 'build' vers votre serveur web\n";
$readme .= "2. Placez-le dans le dossier 'public_html/build'\n";
$readme .= "3. Assurez-vous que les permissions sont correctes:\n";
$readme .= "   - Dossiers: 755\n";
$readme .= "   - Fichiers: 644\n\n";
$readme .= "Structure attendue sur le serveur:\n";
$readme .= "public_html/\n";
$readme .= "├── build/\n";
$readme .= "│   ├── manifest.json\n";
$readme .= "│   └── assets/\n";
$readme .= "│       ├── app-8cgd_IZT.css\n";
$readme .= "│       ├── Home-D-EvkFEK.js\n";
$readme .= "│       └── ... (tous les autres fichiers)\n";
$readme .= "└── ... (autres fichiers Laravel)\n\n";
$readme .= "URLs finales:\n";
$readme .= "- https://votre-domaine.com/build/assets/app-8cgd_IZT.css\n";
$readme .= "- https://votre-domaine.com/build/assets/Home-D-EvkFEK.js\n";

file_put_contents($outputPath . '/README.txt', $readme);

// Lister les fichiers à transférer
echo "\n📋 Fichiers à transférer:\n";
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($outputPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$totalSize = 0;
$fileCount = 0;

foreach ($files as $file) {
    if ($file->isFile()) {
        $relativePath = str_replace($outputPath . '/', '', $file->getPathname());
        $size = $file->getSize();
        $totalSize += $size;
        $fileCount++;
        
        if ($fileCount <= 10) {
            echo "   - $relativePath (" . number_format($size) . " bytes)\n";
        } elseif ($fileCount === 11) {
            echo "   ... et " . (iterator_count($files) - 10) . " autres fichiers\n";
            break;
        }
    }
}

echo "\n📊 Résumé:\n";
echo "   - Nombre de fichiers: $fileCount\n";
echo "   - Taille totale: " . number_format($totalSize) . " bytes (" . round($totalSize / 1024 / 1024, 2) . " MB)\n";
echo "   - Dossier de sortie: $outputPath\n";

echo "\n✅ Préparation terminée!\n";
echo "📤 Transférez maintenant le dossier '$outputPath/build' vers votre serveur web.\n";

?> 