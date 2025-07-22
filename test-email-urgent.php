<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Email Urgent ===\n\n";

// Test avec un email différent
$testEmails = [
    'msapaola@gmail.com',
    'merveillesenga1@gmail.com' // Votre autre email
];

foreach ($testEmails as $email) {
    echo "🧪 Test avec: {$email}\n";
    
    try {
        $subject = "URGENT - Test Email Cabinet Gouverneur - " . date('Y-m-d H:i:s');
        $body = "Ceci est un test URGENT d'envoi d'email.\n\n" .
                "Si vous recevez cet email, cela signifie que :\n" .
                "1. La configuration SMTP fonctionne\n" .
                "2. Les emails arrivent bien\n" .
                "3. Le problème est peut-être dans les spams\n\n" .
                "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
                "Serveur: " . env('MAIL_HOST') . "\n" .
                "From: " . env('MAIL_FROM_ADDRESS') . "\n\n" .
                "Merci de confirmer la réception de cet email.";
        
        Mail::raw($body, function($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->priority(1); // Priorité haute
        });
        
        echo "  ✅ Email URGENT envoyé vers {$email}\n";
        
        // Log de succès
        Log::info("Test email urgent envoyé", [
            'to' => $email,
            'subject' => $subject,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (Exception $e) {
        echo "  ❌ Erreur pour {$email}: " . $e->getMessage() . "\n";
        
        // Log d'erreur
        Log::error("Erreur envoi email urgent", [
            'to' => $email,
            'error' => $e->getMessage(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
    
    echo "\n";
}

echo "=== Test terminé ===\n";
echo "💡 Vérifiez TOUTES les boîtes mail (inbox, spams, promotions)\n";
echo "💡 Répondez-moi si vous recevez l'email URGENT\n"; 