<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test SMTP Simple ===\n\n";

// Afficher la configuration
echo "📧 Configuration SMTP:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER', 'Non défini') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST', 'Non défini') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT', 'Non défini') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME', 'Non défini') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'Non défini') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'Non défini') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME', 'Non défini') . "\n\n";

// Test de connectivité SMTP
echo "🔍 Test de connectivité SMTP...\n";

try {
    // Utiliser la façade Mail de Laravel au lieu de Swift directement
    echo "📤 Test d'envoi d'email...\n";
    
    Mail::raw('Ceci est un test d\'envoi d\'email depuis le système de rendez-vous du Cabinet du Gouverneur de Kinshasa.' . "\n\n" . 'Date: ' . date('Y-m-d H:i:s') . "\n" . 'Serveur: ' . env('MAIL_HOST'), function($message) {
        $message->to('msapaola@gmail.com')
                ->subject('Test SMTP - Cabinet du Gouverneur')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    
    echo "✅ Email envoyé avec succès à msapaola@gmail.com !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur SMTP: " . $e->getMessage() . "\n";
    echo "🔍 Détails: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test terminé ===\n"; 