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
    $transport = new \Swift_SmtpTransport(
        env('MAIL_HOST'),
        env('MAIL_PORT'),
        env('MAIL_ENCRYPTION')
    );
    
    $transport->setUsername(env('MAIL_USERNAME'));
    $transport->setPassword(env('MAIL_PASSWORD'));
    
    // Test de connexion
    $mailer = new \Swift_Mailer($transport);
    $mailer->getTransport()->start();
    
    echo "✅ Connexion SMTP réussie !\n";
    
    // Test d'envoi d'email
    echo "📤 Test d'envoi d'email...\n";
    
    $message = new \Swift_Message();
    $message->setSubject('Test SMTP - Cabinet du Gouverneur');
    $message->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_NAME')]);
    $message->setTo(['test@example.com' => 'Test User']);
    $message->setBody('Ceci est un test d\'envoi d\'email depuis le système de rendez-vous du Cabinet du Gouverneur de Kinshasa.' . "\n\n" . 'Date: ' . date('Y-m-d H:i:s') . "\n" . 'Serveur: ' . env('MAIL_HOST'));
    
    $result = $mailer->send($message);
    
    if ($result) {
        echo "✅ Email envoyé avec succès !\n";
        echo "📧 Nombre d'emails envoyés: $result\n";
    } else {
        echo "❌ Échec de l'envoi d'email\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur SMTP: " . $e->getMessage() . "\n";
    echo "🔍 Détails: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test terminé ===\n"; 