<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test d'envoi d'email pour rendez-vous accepté ===\n\n";

// Trouver un rendez-vous en attente
$appointment = Appointment::where('status', 'pending')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous en attente trouvé.\n";
    echo "Créons un rendez-vous de test...\n";
    
    $appointment = Appointment::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+243123456789',
        'subject' => 'Test de notification',
        'message' => 'Ceci est un test pour vérifier l\'envoi d\'email.',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    
    echo "✅ Rendez-vous de test créé (ID: {$appointment->id})\n";
}

// Trouver un utilisateur admin
$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé.\n";
    exit(1);
}

echo "📧 Test d'envoi de notification pour le rendez-vous #{$appointment->id}\n";
echo "👤 Demandeur: {$appointment->name} ({$appointment->email})\n";
echo "🔗 Token de suivi: {$appointment->secure_token}\n";
echo "🔗 URL de suivi: " . route('appointments.tracking', $appointment->secure_token) . "\n\n";

// Simuler l'acceptation du rendez-vous
$oldStatus = $appointment->status;
$appointment->update([
    'status' => 'accepted',
    'processed_by' => $admin->id,
    'processed_at' => now(),
]);

echo "✅ Rendez-vous marqué comme accepté\n";

// Envoyer la notification
try {
    $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
    echo "✅ Notification envoyée avec succès !\n";
    echo "📝 L'email a été enregistré dans les logs (storage/logs/laravel.log)\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi de la notification: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n"; 