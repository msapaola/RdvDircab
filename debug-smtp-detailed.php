<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic SMTP Détaillé ===\n\n";

// 1. Configuration actuelle
echo "📧 Configuration SMTP actuelle:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER', 'Non défini') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST', 'Non défini') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT', 'Non défini') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME', 'Non défini') . "\n";
echo "MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? '***Défini***' : 'Non défini') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'Non défini') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'Non défini') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME', 'Non défini') . "\n\n";

// 2. Test de connectivité SMTP basique
echo "🔍 Test de connectivité SMTP...\n";
try {
    $host = env('MAIL_HOST');
    $port = env('MAIL_PORT');
    
    echo "Tentative de connexion à {$host}:{$port}...\n";
    
    $connection = fsockopen($host, $port, $errno, $errstr, 10);
    if ($connection) {
        echo "✅ Connexion TCP réussie\n";
        fclose($connection);
    } else {
        echo "❌ Échec de connexion TCP: $errstr ($errno)\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Test d'envoi avec gestion d'erreur détaillée
echo "🧪 Test d'envoi d'email avec gestion d'erreur...\n";

try {
    // Activer les logs détaillés
    Log::info('Début du test d\'envoi d\'email', [
        'timestamp' => now()->toDateTimeString(),
        'to' => 'msapaola@gmail.com',
        'from' => env('MAIL_FROM_ADDRESS')
    ]);
    
    Mail::raw('Test d\'envoi d\'email détaillé - ' . date('Y-m-d H:i:s') . "\n\n" . 
              'Ceci est un test pour vérifier l\'envoi d\'email depuis le serveur de production.' . "\n" .
              'Configuration SMTP:' . "\n" .
              '- Host: ' . env('MAIL_HOST') . "\n" .
              '- Port: ' . env('MAIL_PORT') . "\n" .
              '- Encryption: ' . env('MAIL_ENCRYPTION') . "\n" .
              '- From: ' . env('MAIL_FROM_ADDRESS') . "\n\n" .
              'Si vous recevez cet email, la configuration SMTP fonctionne correctement.', 
        function($message) {
            $message->to('msapaola@gmail.com')
                    ->subject('Test SMTP Détaillé - Cabinet du Gouverneur')
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    
    echo "✅ Email envoyé (selon Laravel)\n";
    Log::info('Email envoyé avec succès (selon Laravel)', [
        'timestamp' => now()->toDateTimeString(),
        'to' => 'msapaola@gmail.com'
    ]);
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi: " . $e->getMessage() . "\n";
    echo "🔍 Type d'erreur: " . get_class($e) . "\n";
    echo "📝 Trace: " . $e->getTraceAsString() . "\n";
    
    Log::error('Erreur d\'envoi d\'email', [
        'error' => $e->getMessage(),
        'type' => get_class($e),
        'trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString()
    ]);
}

echo "\n";

// 4. Vérifier la configuration mail.php
echo "🔍 Vérification de la configuration mail.php...\n";
$mailConfig = config('mail');
echo "Default mailer: " . ($mailConfig['default'] ?? 'Non défini') . "\n";
echo "Mailers disponibles: " . implode(', ', array_keys($mailConfig['mailers'] ?? [])) . "\n";

if (isset($mailConfig['mailers']['smtp'])) {
    echo "Configuration SMTP trouvée:\n";
    echo "  - Transport: " . ($mailConfig['mailers']['smtp']['transport'] ?? 'Non défini') . "\n";
    echo "  - Host: " . ($mailConfig['mailers']['smtp']['host'] ?? 'Non défini') . "\n";
    echo "  - Port: " . ($mailConfig['mailers']['smtp']['port'] ?? 'Non défini') . "\n";
    echo "  - Encryption: " . ($mailConfig['mailers']['smtp']['encryption'] ?? 'Non défini') . "\n";
}

echo "\n";

// 5. Test avec différents mailers
echo "🧪 Test avec différents mailers...\n";

$mailers = ['smtp', 'log', 'array'];

foreach ($mailers as $mailer) {
    echo "Test avec mailer: {$mailer}\n";
    
    try {
        config(['mail.default' => $mailer]);
        
        Mail::raw("Test avec mailer {$mailer} - " . date('Y-m-d H:i:s'), function($message) {
            $message->to('msapaola@gmail.com')
                    ->subject("Test Mailer {$mailer}")
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        
        echo "  ✅ Mailer {$mailer} fonctionne\n";
        
    } catch (Exception $e) {
        echo "  ❌ Mailer {$mailer} échoue: " . $e->getMessage() . "\n";
    }
}

// Remettre la configuration originale
config(['mail.default' => env('MAIL_MAILER', 'smtp')]);

echo "\n";

// 6. Vérifier les logs récents
echo "📝 Logs récents d'email:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -50);
    
    $emailLogs = [];
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || 
            strpos($line, 'email') !== false || 
            strpos($line, 'smtp') !== false ||
            strpos($line, 'mail') !== false) {
            $emailLogs[] = $line;
        }
    }
    
    if (!empty($emailLogs)) {
        foreach (array_slice($emailLogs, -10) as $log) {
            echo "  " . $log . "\n";
        }
    } else {
        echo "  Aucun log d'email récent trouvé\n";
    }
} else {
    echo "  Fichier de log non trouvé\n";
}

echo "\n=== Diagnostic terminé ===\n";
echo "💡 Suggestions:\n";
echo "1. Vérifiez les spams de msapaola@gmail.com\n";
echo "2. Testez avec une autre adresse email\n";
echo "3. Vérifiez la configuration SMTP du serveur\n";
echo "4. Consultez les logs du serveur SMTP\n"; 