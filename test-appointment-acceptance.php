<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test d'acceptation de rendez-vous avec envoi d'email ===\n\n";

// Trouver ou créer un rendez-vous de test
$appointment = Appointment::where('email', 'msapaola@gmail.com')->first();

if (!$appointment) {
    echo "📝 Création d'un rendez-vous de test pour msapaola@gmail.com...\n";
    
    $appointment = Appointment::create([
        'name' => 'Merveille Senga',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test d\'envoi d\'email de confirmation',
        'message' => 'Ceci est un test pour vérifier l\'envoi d\'email lors de l\'acceptation d\'un rendez-vous.',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    
    echo "✅ Rendez-vous de test créé (ID: {$appointment->id})\n";
} else {
    echo "📋 Rendez-vous existant trouvé (ID: {$appointment->id})\n";
}

// Trouver un utilisateur admin
$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit(1);
}

echo "👤 Admin: {$admin->name} ({$admin->email})\n\n";

// Afficher les détails du rendez-vous
echo "📋 Détails du rendez-vous:\n";
echo "   ID: {$appointment->id}\n";
echo "   Nom: {$appointment->name}\n";
echo "   Email: {$appointment->email}\n";
echo "   Sujet: {$appointment->subject}\n";
echo "   Date: {$appointment->preferred_date}\n";
echo "   Heure: {$appointment->preferred_time}\n";
echo "   Statut actuel: {$appointment->status}\n";
echo "   Token: {$appointment->secure_token}\n\n";

// Simuler l'acceptation du rendez-vous
echo "🔄 Simulation de l'acceptation du rendez-vous...\n";

try {
    // Sauvegarder l'ancien statut
    $oldStatus = $appointment->status;
    
    // Marquer comme accepté
    $appointment->update([
        'status' => 'accepted',
        'processed_by' => $admin->id,
        'processed_at' => now(),
    ]);
    
    echo "✅ Rendez-vous marqué comme accepté\n";
    
    // Envoyer la notification
    echo "📧 Envoi de la notification...\n";
    $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
    
    echo "✅ Notification envoyée avec succès !\n";
    echo "📧 Email envoyé à: {$appointment->email}\n";
    echo "🔗 Lien de suivi: " . route('appointments.tracking', $appointment->secure_token) . "\n";
    
    // Log de succès
    Log::info('Test d\'acceptation réussi - Email envoyé', [
        'appointment_id' => $appointment->id,
        'recipient_email' => $appointment->email,
        'tracking_url' => route('appointments.tracking', $appointment->secure_token),
        'admin' => $admin->name,
        'timestamp' => now()->toDateTimeString(),
    ]);
    
    echo "\n🎉 Test réussi ! Vérifiez la boîte mail msapaola@gmail.com\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    
    // Log de l'erreur
    Log::error('Test d\'acceptation échoué', [
        'appointment_id' => $appointment->id,
        'recipient_email' => $appointment->email,
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString(),
    ]);
}

echo "\n=== Test terminé ===\n";
echo "📝 Consultez les logs dans storage/logs/laravel.log pour plus de détails\n"; 