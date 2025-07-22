<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Email Simple ===\n\n";

// Configuration
echo "📧 Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER', 'Non défini') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST', 'Non défini') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT', 'Non défini') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'Non défini') . "\n\n";

// Test simple
echo "🧪 Test d'envoi d'email...\n";

try {
    Mail::raw('Test email simple - ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('msapaola@gmail.com')
                ->subject('Test Email Simple')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    
    echo "✅ Email envoyé avec succès !\n";
    echo "📧 Vérifiez la boîte mail msapaola@gmail.com\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n"; 