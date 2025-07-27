<?php

// Fichier : test-email.php (à placer dans la racine du projet Laravel)

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

echo "=== Test d'envoi d'email ===\n";
echo "Destinataire: msapaola@gmail.com\n";
echo "Configuration SMTP en cours...\n\n";

try {
    Mail::raw('Ceci est un email de test envoyé depuis l\'application Laravel du Cabinet du Gouverneur.', function (Message $message) {
        $message->to('msapaola@gmail.com')
                ->subject('Test d\'envoi d\'email - Cabinet du Gouverneur')
                ->from(config('mail.from.address'), config('mail.from.name'));
    });
    
    echo "✅ Email envoyé avec succès à msapaola@gmail.com\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi de l'email:\n";
    echo $e->getMessage() . "\n";
    
    echo "\n=== Informations de débogage ===\n";
    echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
    echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
    echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
    echo "MAIL_FROM: " . config('mail.from.address') . "\n";
}

echo "\nTest terminé.\n";

?>