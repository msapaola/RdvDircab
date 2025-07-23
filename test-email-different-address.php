<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Email Adresses Différentes ===\n\n";

// Test avec différentes adresses
$testEmails = [
    'msapaola@gmail.com',
    'merveillesenga1@gmail.com',
    'test@gmail.com', // Si vous avez une autre adresse
];

foreach ($testEmails as $email) {
    echo "🧪 Test avec: {$email}\n";
    
    // 1. Test email simple
    echo "   📧 Test email simple...\n";
    try {
        Mail::raw('Test email simple vers ' . $email . ' - ' . date('Y-m-d H:i:s'), function($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email Simple - ' . $email)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        echo "   ✅ Email simple envoyé\n";
    } catch (Exception $e) {
        echo "   ❌ Erreur email simple: " . $e->getMessage() . "\n";
    }
    
    // 2. Test notification
    echo "   📧 Test notification...\n";
    try {
        // Créer un rendez-vous de test
        $appointment = Appointment::create([
            'name' => 'Test ' . $email,
            'email' => $email,
            'phone' => '+243123456789',
            'subject' => 'Test Notification ' . $email,
            'message' => 'Test pour ' . $email,
            'preferred_date' => now()->addDays(7),
            'preferred_time' => '10:00',
            'priority' => 'normal',
            'status' => 'accepted',
            'secure_token' => \Illuminate\Support\Str::uuid(),
        ]);
        
        // Envoyer la notification
        $appointment->notify(new AppointmentStatusUpdate($appointment, 'pending'));
        echo "   ✅ Notification envoyée\n";
        
        // Log
        Log::info("Test notification envoyée", [
            'email' => $email,
            'appointment_id' => $appointment->id,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } catch (Exception $e) {
        echo "   ❌ Erreur notification: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Test terminé ===\n";
echo "💡 Vérifiez TOUTES les adresses email (inbox, spams, promotions)\n";
echo "💡 Dites-moi quelles adresses reçoivent les emails\n"; 