<?php

echo "=== Désactivation temporaire des emails ===\n\n";

// Vérifier si Laravel peut démarrer
echo "🔍 Test de démarrage de Laravel...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel démarre correctement\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du démarrage de Laravel :\n";
    echo "   Message : {$e->getMessage()}\n";
    exit;
}

// Créer une configuration temporaire pour désactiver les emails
echo "\n🔍 Création de configuration temporaire...\n";

$configPath = 'config/mail.php';
if (file_exists($configPath)) {
    echo "✅ Fichier de configuration mail trouvé\n";
    
    // Sauvegarder la configuration actuelle
    $backupPath = 'config/mail.php.backup.' . date('Y-m-d-H-i-s');
    if (copy($configPath, $backupPath)) {
        echo "✅ Configuration sauvegardée dans {$backupPath}\n";
    } else {
        echo "⚠️  Impossible de sauvegarder la configuration\n";
    }
    
    // Lire la configuration actuelle
    $config = require $configPath;
    
    // Modifier la configuration pour utiliser le driver log
    $config['default'] = 'log';
    $config['mailers']['log'] = [
        'transport' => 'log',
        'channel' => env('LOG_CHANNEL', 'stack'),
    ];
    
    // Écrire la nouvelle configuration
    $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
    if (file_put_contents($configPath, $configContent)) {
        echo "✅ Configuration modifiée pour utiliser le driver log\n";
    } else {
        echo "❌ Impossible de modifier la configuration\n";
    }
    
} else {
    echo "❌ Fichier de configuration mail non trouvé\n";
}

// Vérifier la nouvelle configuration
echo "\n🔍 Vérification de la nouvelle configuration...\n";
try {
    $newConfig = config('mail');
    echo "✅ Nouvelle configuration chargée\n";
    echo "   Driver par défaut : " . ($newConfig['default'] ?? 'non défini') . "\n";
    
    if (isset($newConfig['mailers']['log'])) {
        echo "   Driver log configuré : Oui\n";
    } else {
        echo "   Driver log configuré : Non\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Créer un fichier .env temporaire pour les tests
echo "\n🔍 Création de .env temporaire...\n";
$envPath = '.env';
$envBackupPath = '.env.backup.' . date('Y-m-d-H-i-s');

if (file_exists($envPath)) {
    // Sauvegarder le .env actuel
    if (copy($envPath, $envBackupPath)) {
        echo "✅ .env sauvegardé dans {$envBackupPath}\n";
    }
    
    // Lire le contenu actuel
    $envContent = file_get_contents($envPath);
    
    // Modifier les variables mail
    $envContent = preg_replace('/MAIL_MAILER=.*/', 'MAIL_MAILER=log', $envContent);
    $envContent = preg_replace('/MAIL_HOST=.*/', '# MAIL_HOST=gouv.kinshasa.cd', $envContent);
    $envContent = preg_replace('/MAIL_PORT=.*/', '# MAIL_PORT=465', $envContent);
    
    // Ajouter un commentaire
    $envContent .= "\n# Configuration temporaire - emails désactivés\n";
    $envContent .= "# Les emails seront enregistrés dans storage/logs/laravel.log\n";
    
    // Écrire le nouveau .env
    if (file_put_contents($envPath, $envContent)) {
        echo "✅ .env modifié pour désactiver les emails\n";
    } else {
        echo "❌ Impossible de modifier le .env\n";
    }
    
} else {
    echo "⚠️  Fichier .env non trouvé\n";
}

// Instructions pour restaurer
echo "\n📋 Instructions pour restaurer la configuration :\n";
echo "1. Restaurer le fichier de configuration :\n";
echo "   cp config/mail.php.backup.* config/mail.php\n";
echo "\n2. Restaurer le fichier .env :\n";
echo "   cp .env.backup.* .env\n";
echo "\n3. Vider le cache de configuration :\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";

echo "\n✅ Configuration temporaire appliquée\n";
echo "⚠️  Les emails seront maintenant enregistrés dans les logs au lieu d'être envoyés\n";
echo "📝 Vérifiez storage/logs/laravel.log pour voir les emails\n";

echo "\n=== Désactivation terminée ===\n"; 