<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic Email Production ===\n\n";

// 1. Vérifier la configuration
echo "📧 Configuration SMTP:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER', 'Non défini') . "\n";
echo "MAIL_HOST: " . env('MAIL_HOST', 'Non défini') . "\n";
echo "MAIL_PORT: " . env('MAIL_PORT', 'Non défini') . "\n";
echo "MAIL_USERNAME: " . env('MAIL_USERNAME', 'Non défini') . "\n";
echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION', 'Non défini') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS', 'Non défini') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME', 'Non défini') . "\n\n";

// 2. Test d'envoi d'email simple
echo "🧪 Test 1: Email simple\n";
try {
    Mail::raw('Test email simple - ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('msapaola@gmail.com')
                ->subject('Test Email Simple - Production')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    echo "✅ Email simple envoyé avec succès\n";
} catch (Exception $e) {
    echo "❌ Erreur email simple: " . $e->getMessage() . "\n";
    Log::error('Test email simple échoué', ['error' => $e->getMessage()]);
}

echo "\n";

// 3. Trouver un rendez-vous existant
echo "🔍 Recherche d'un rendez-vous existant...\n";
$appointment = Appointment::where('email', 'msapaola@gmail.com')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous trouvé pour msapaola@gmail.com\n";
    echo "Création d'un rendez-vous de test...\n";
    
    $appointment = Appointment::create([
        'name' => 'Test User',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test Diagnostic',
        'message' => 'Test pour diagnostic email',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    echo "✅ Rendez-vous créé (ID: {$appointment->id})\n";
} else {
    echo "✅ Rendez-vous trouvé (ID: {$appointment->id}, Statut: {$appointment->status})\n";
}

// 4. Vérifier que le modèle peut recevoir des notifications
echo "\n🔍 Vérification du modèle Appointment...\n";
echo "Trait Notifiable: " . (in_array('Illuminate\Notifications\Notifiable', class_uses($appointment)) ? '✅ Présent' : '❌ Absent') . "\n";
echo "Méthode routeNotificationForMail: " . (method_exists($appointment, 'routeNotificationForMail') ? '✅ Présente' : '❌ Absente') . "\n";

// 5. Test de la méthode routeNotificationForMail
echo "\n🧪 Test 2: Méthode routeNotificationForMail\n";
try {
    $email = $appointment->routeNotificationForMail(new AppointmentStatusUpdate($appointment, 'pending'));
    echo "✅ Email retourné: {$email}\n";
} catch (Exception $e) {
    echo "❌ Erreur routeNotificationForMail: " . $e->getMessage() . "\n";
}

// 6. Test de création de notification
echo "\n🧪 Test 3: Création de notification\n";
try {
    $notification = new AppointmentStatusUpdate($appointment, 'pending');
    echo "✅ Notification créée avec succès\n";
    echo "   - Classe: " . get_class($notification) . "\n";
    echo "   - Canaux: " . implode(', ', $notification->via($appointment)) . "\n";
} catch (Exception $e) {
    echo "❌ Erreur création notification: " . $e->getMessage() . "\n";
}

// 7. Test d'envoi de notification
echo "\n🧪 Test 4: Envoi de notification\n";
try {
    echo "Envoi de la notification...\n";
    $appointment->notify(new AppointmentStatusUpdate($appointment, 'pending'));
    echo "✅ Notification envoyée avec succès !\n";
    
    // Log de succès
    Log::info('Test notification réussi', [
        'appointment_id' => $appointment->id,
        'email' => $appointment->email,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
} catch (Exception $e) {
    echo "❌ Erreur envoi notification: " . $e->getMessage() . "\n";
    echo "🔍 Trace: " . $e->getTraceAsString() . "\n";
    
    // Log de l'erreur
    Log::error('Test notification échoué', [
        'appointment_id' => $appointment->id,
        'email' => $appointment->email,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString(),
    ]);
}

// 8. Vérifier les logs récents
echo "\n📝 Logs récents:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -20);
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || strpos($line, 'notification') !== false) {
            echo "   " . $line . "\n";
        }
    }
} else {
    echo "   Aucun fichier de log trouvé\n";
}

echo "\n=== Diagnostic terminé ===\n"; 