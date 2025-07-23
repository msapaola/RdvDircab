<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Email de Notification ===\n\n";

// Créer un rendez-vous de test
$appointment = Appointment::create([
    'name' => 'Test Notification User',
    'email' => 'msapaola@gmail.com',
    'phone' => '+243123456789',
    'subject' => 'Test Notification Email',
    'message' => 'Test pour vérifier l\'email de notification',
    'preferred_date' => now()->addDays(7),
    'preferred_time' => '10:00',
    'priority' => 'normal',
    'status' => 'accepted',
    'secure_token' => \Illuminate\Support\Str::uuid(),
]);

echo "📋 Rendez-vous créé (ID: {$appointment->id})\n";
echo "📧 Email: {$appointment->email}\n";
echo "🔗 Token: {$appointment->secure_token}\n\n";

// Test 1: Envoyer l'email de notification exact
echo "🧪 Test 1: Email de notification exact\n";
try {
    $appointment->notify(new AppointmentStatusUpdate($appointment, 'pending'));
    echo "✅ Notification envoyée avec succès !\n";
    echo "📧 Vérifiez msapaola@gmail.com\n";
} catch (Exception $e) {
    echo "❌ Erreur notification: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Envoyer un email manuel avec le même contenu
echo "🧪 Test 2: Email manuel avec contenu similaire\n";
try {
    $trackingUrl = route('appointments.tracking', $appointment->secure_token);
    
    $subject = 'Mise à jour de votre demande de rendez-vous - Cabinet du Gouverneur';
    $body = "Bonjour Test Notification User,\n\n" .
            "Nous avons le plaisir de vous informer que votre demande de rendez-vous a été acceptée.\n\n" .
            "Détails du rendez-vous :\n" .
            "• Objet : Test Notification Email\n" .
            "• Date : " . $appointment->preferred_date . "\n" .
            "• Heure : " . $appointment->preferred_time . "\n" .
            "• Statut : Accepté\n\n" .
            "Instructions importantes :\n" .
            "• Veuillez vous présenter 15 minutes avant l'heure du rendez-vous\n" .
            "• Apportez une pièce d'identité valide\n" .
            "• En cas d'empêchement, veuillez nous contacter au plus tôt\n\n" .
            "Lien de suivi : " . $trackingUrl . "\n\n" .
            "Pour toute question, veuillez nous contacter.\n\n" .
            "Cordialement,\n" .
            "Le Cabinet du Gouverneur de Kinshasa";
    
    Mail::raw($body, function($message) use ($subject) {
        $message->to('msapaola@gmail.com')
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    
    echo "✅ Email manuel envoyé avec succès !\n";
    echo "📧 Vérifiez msapaola@gmail.com\n";
    
} catch (Exception $e) {
    echo "❌ Erreur email manuel: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Envoyer avec un sujet différent
echo "🧪 Test 3: Email avec sujet différent\n";
try {
    $subject = 'Votre rendez-vous a été confirmé - Cabinet du Gouverneur';
    $body = "Bonjour,\n\n" .
            "Votre rendez-vous a été confirmé.\n\n" .
            "Lien de suivi : " . route('appointments.tracking', $appointment->secure_token) . "\n\n" .
            "Merci.";
    
    Mail::raw($body, function($message) use ($subject) {
        $message->to('msapaola@gmail.com')
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    
    echo "✅ Email avec sujet différent envoyé !\n";
    echo "📧 Vérifiez msapaola@gmail.com\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
echo "💡 Vérifiez msapaola@gmail.com pour les 3 emails\n";
echo "💡 Dites-moi lesquels arrivent et lesquels n'arrivent pas\n"; 