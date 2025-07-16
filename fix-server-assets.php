<?php

// Script pour corriger l'accès aux assets sur le serveur
echo "=== Correction de l'accès aux assets ===\n\n";

$publicHtml = __DIR__;
$buildPath = $publicHtml . '/public/build';
$buildLink = $publicHtml . '/build';

echo "1. Vérification de la structure...\n";
echo "   📁 Public HTML: $publicHtml\n";
echo "   📁 Build path: $buildPath\n";
echo "   🔗 Build link: $buildLink\n\n";

// Vérifier si le dossier build existe
if (!is_dir($buildPath)) {
    echo "❌ Erreur: Le dossier public/build n'existe pas!\n";
    exit(1);
}

// Vérifier si le lien symbolique existe déjà
if (is_link($buildLink)) {
    echo "2. Suppression de l'ancien lien symbolique...\n";
    unlink($buildLink);
    echo "   ✅ Ancien lien supprimé\n";
}

// Créer le lien symbolique
echo "\n3. Création du lien symbolique...\n";
if (symlink($buildPath, $buildLink)) {
    echo "   ✅ Lien symbolique créé: $buildLink -> $buildPath\n";
} else {
    echo "   ❌ Impossible de créer le lien symbolique\n";
    echo "   🔄 Tentative de copie du dossier...\n";
    
    // Si le lien symbolique ne fonctionne pas, copier le dossier
    if (is_dir($buildLink)) {
        system("rm -rf $buildLink");
    }
    
    if (system("cp -r $buildPath $buildLink") === false) {
        echo "   ❌ Impossible de copier le dossier\n";
        exit(1);
    } else {
        echo "   ✅ Dossier copié avec succès\n";
    }
}

// Vérifier les permissions
echo "\n4. Vérification des permissions...\n";
system("chmod -R 755 $buildLink");
system("find $buildLink -type f -exec chmod 644 {} \\;");
echo "   ✅ Permissions mises à jour\n";

// Test d'accès
echo "\n5. Test d'accès aux assets...\n";
$testFiles = [
    'build/assets/app-8cgd_IZT.css',
    'build/assets/app-pg7X1LG8.js',
    'build/manifest.json'
];

foreach ($testFiles as $file) {
    $fullPath = $publicHtml . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "   ✅ $file ($size bytes)\n";
    } else {
        echo "   ❌ $file (manquant)\n";
    }
}

echo "\n=== Correction terminée ===\n";
echo "🎯 Les assets devraient maintenant être accessibles via:\n";
echo "   https://votre-domaine.com/build/assets/fichier.js\n";
echo "   https://votre-domaine.com/build/manifest.json\n\n";

echo "📝 Si le problème persiste, vérifiez:\n";
echo "   1. Que votre hébergeur autorise les liens symboliques\n";
echo "   2. Que le serveur web a les permissions d'accès\n";
echo "   3. Que les fichiers .htaccess n'interfèrent pas\n"; 