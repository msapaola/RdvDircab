<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

// Charger la configuration Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de Configuration Email ===\n\n";

// Afficher la configuration actuelle
echo "Configuration actuelle :\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . (config('mail.mailers.smtp.encryption') ?? 'non défini') . "\n\n";

// Test avec différents mailers
$mailers = ['smtp', 'log', 'array'];

foreach ($mailers as $mailer) {
    echo "=== Test avec mailer: $mailer ===\n";
    
    try {
        // Configurer le mailer
        Config::set('mail.default', $mailer);
        
        // Créer un message simple
        $message = "Test d'envoi d'email avec mailer: $mailer\n";
        $message .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $message .= "Serveur: " . ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost') . "\n";
        
        // Envoyer l'email
        Mail::raw($message, function($msg) {
            $msg->to('test@example.com')
                ->subject('Test Configuration Email - ' . config('mail.default'));
        });
        
        echo "✅ Email envoyé avec succès avec le mailer: $mailer\n";
        
        if ($mailer === 'log') {
            echo "📝 Vérifiez le fichier storage/logs/laravel.log pour voir l'email\n";
        } elseif ($mailer === 'array') {
            echo "📧 Email stocké en mémoire (mode test)\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur avec le mailer $mailer: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test spécifique pour les notifications
echo "=== Test Notification ===\n";

try {
    // Créer un appointment de test
    $appointment = new \App\Models\Appointment([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subject' => 'Test Notification',
        'preferred_date' => '2025-07-30',
        'preferred_time' => '10:00',
        'status' => 'accepted',
        'formatted_status' => 'Accepté',
        'formatted_priority' => 'Normal'
    ]);
    
    // Envoyer la notification
    \Illuminate\Support\Facades\Notification::route('mail', 'test@example.com')
        ->notify(new \App\Notifications\AppointmentStatusUpdate($appointment));
    
    echo "✅ Notification envoyée avec succès\n";
    
} catch (Exception $e) {
    echo "❌ Erreur notification: " . $e->getMessage() . "\n";
}

echo "\n=== Fin des tests ===\n"; 