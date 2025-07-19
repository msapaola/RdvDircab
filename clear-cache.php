<?php

echo "🧹 Nettoyage du cache et des assets...\n";

// Nettoyer le cache Laravel
echo "1. Nettoyage du cache Laravel...\n";
system('php artisan cache:clear');
system('php artisan config:clear');
system('php artisan route:clear');
system('php artisan view:clear');

// Nettoyer le cache Vite
echo "2. Nettoyage du cache Vite...\n";
system('rm -rf node_modules/.vite');

// Reconstruire les assets
echo "3. Reconstruction des assets...\n";
system('npm run build');

// Vérifier les nouveaux assets
echo "4. Vérification des nouveaux assets...\n";
$manifest = json_decode(file_get_contents('public/build/manifest.json'), true);
if ($manifest) {
    echo "✅ Manifest mis à jour avec succès\n";
    echo "📁 Nouveaux assets générés:\n";
    foreach ($manifest as $key => $value) {
        if (isset($value['file']) && strpos($value['file'], 'app-') === 0) {
            echo "   - {$value['file']}\n";
        }
    }
} else {
    echo "❌ Erreur lors de la lecture du manifest\n";
}

echo "\n🎉 Nettoyage terminé !\n";
echo "💡 Conseil: Videz le cache de votre navigateur (Ctrl+F5 ou Cmd+Shift+R)\n"; 