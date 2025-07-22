<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Email avec Adresse Alternative ===\n\n";

// Configuration
echo "📧 Configuration SMTP:\n";
echo "Host: " . env('MAIL_HOST') . "\n";
echo "Port: " . env('MAIL_PORT') . "\n";
echo "From: " . env('MAIL_FROM_ADDRESS') . "\n\n";

// Test avec différentes adresses
$testEmails = [
    'msapaola@gmail.com',
    'merveillesenga1@gmail.com', // Votre autre email
    'test@example.com', // Email de test générique
];

foreach ($testEmails as $email) {
    echo "🧪 Test avec: {$email}\n";
    
    try {
        $subject = "Test Email - " . date('Y-m-d H:i:s');
        $body = "Ceci est un test d'envoi d'email vers {$email}\n\n" .
                "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
                "Serveur: " . env('MAIL_HOST') . "\n" .
                "From: " . env('MAIL_FROM_ADDRESS') . "\n\n" .
                "Si vous recevez cet email, la configuration SMTP fonctionne.";
        
        Mail::raw($body, function($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        
        echo "  ✅ Email envoyé vers {$email}\n";
        
        // Log de succès
        Log::info("Test email envoyé", [
            'to' => $email,
            'timestamp' => now()->toDateTimeString(),
            'subject' => $subject
        ]);
        
    } catch (Exception $e) {
        echo "  ❌ Erreur pour {$email}: " . $e->getMessage() . "\n";
        
        // Log d'erreur
        Log::error("Erreur envoi email", [
            'to' => $email,
            'error' => $e->getMessage(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
    
    echo "\n";
}

echo "=== Test terminé ===\n";
echo "💡 Vérifiez toutes les boîtes mail pour voir si certains emails arrivent\n"; 