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

echo "=== Test d'envoi d'email SMTP ===\n\n";

// Configuration SMTP
echo "📧 Configuration SMTP:\n";
echo "Host: " . env('MAIL_HOST', 'Non défini') . "\n";
echo "Port: " . env('MAIL_PORT', 'Non défini') . "\n";
echo "Username: " . env('MAIL_USERNAME', 'Non défini') . "\n";
echo "Encryption: " . env('MAIL_ENCRYPTION', 'Non défini') . "\n";
echo "From: " . env('MAIL_FROM_ADDRESS', 'Non défini') . "\n";
echo "From Name: " . env('MAIL_FROM_NAME', 'Non défini') . "\n\n";

// Test 1: Envoi d'email simple
echo "🧪 Test 1: Envoi d'email simple\n";
try {
    Mail::raw('Ceci est un test d\'envoi d\'email depuis le système de rendez-vous du Cabinet du Gouverneur.', function($message) {
        $message->to('test@example.com')
                ->subject('Test SMTP - Cabinet du Gouverneur')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    echo "✅ Email simple envoyé avec succès\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi d'email simple: " . $e->getMessage() . "\n";
    Log::error('Test SMTP échoué - Email simple', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

echo "\n";

// Test 2: Envoi de notification de rendez-vous
echo "🧪 Test 2: Envoi de notification de rendez-vous\n";

// Trouver un rendez-vous existant ou en créer un
$appointment = Appointment::first();
if (!$appointment) {
    echo "❌ Aucun rendez-vous trouvé dans la base de données\n";
    exit(1);
}

echo "📋 Rendez-vous utilisé pour le test:\n";
echo "   ID: {$appointment->id}\n";
echo "   Nom: {$appointment->name}\n";
echo "   Email: {$appointment->email}\n";
echo "   Statut: {$appointment->status}\n";
echo "   Token: {$appointment->secure_token}\n\n";

// Trouver un utilisateur admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit(1);
}

echo "👤 Admin: {$admin->name} ({$admin->email})\n\n";

// Simuler l'acceptation et envoi de notification
try {
    // Sauvegarder l'ancien statut
    $oldStatus = $appointment->status;
    
    // Marquer comme accepté temporairement pour le test
    $appointment->update([
        'status' => 'accepted',
        'processed_by' => $admin->id,
        'processed_at' => now(),
    ]);
    
    echo "✅ Rendez-vous marqué comme accepté pour le test\n";
    
    // Envoyer la notification
    $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
    
    echo "✅ Notification envoyée avec succès !\n";
    echo "📧 Email envoyé à: {$appointment->email}\n";
    echo "🔗 Lien de suivi: " . route('appointments.tracking', $appointment->secure_token) . "\n";
    
    // Log de succès
    Log::info('Test SMTP réussi - Notification de rendez-vous', [
        'appointment_id' => $appointment->id,
        'recipient_email' => $appointment->email,
        'tracking_url' => route('appointments.tracking', $appointment->secure_token),
        'admin' => $admin->name,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    // Remettre le statut original
    $appointment->update(['status' => $oldStatus]);
    echo "✅ Statut du rendez-vous restauré\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi de la notification: " . $e->getMessage() . "\n";
    
    // Log de l'erreur
    Log::error('Test SMTP échoué - Notification de rendez-vous', [
        'appointment_id' => $appointment->id,
        'recipient_email' => $appointment->email,
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    // Remettre le statut original en cas d'erreur
    $appointment->update(['status' => $oldStatus]);
}

echo "\n=== Test terminé ===\n";
echo "📝 Consultez les logs dans storage/logs/laravel.log pour plus de détails\n"; 