<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Nettoyage et création d'un rendez-vous de test ===\n\n";

// Supprimer le rendez-vous de test mal créé (ID 20)
$testAppointment = Appointment::find(20);
if ($testAppointment) {
    echo "🗑️ Suppression du rendez-vous de test ID 20...\n";
    $testAppointment->delete();
    echo "✅ Rendez-vous supprimé\n\n";
}

// Créer un nouveau rendez-vous de test avec des données propres
echo "📝 Création d'un nouveau rendez-vous de test...\n";

$appointment = Appointment::create([
    'name' => 'Test Utilisateur',
    'email' => 'test@example.com',
    'phone' => '0123456789',
    'subject' => 'Test d\'annulation',
    'message' => 'Ceci est un test pour vérifier l\'annulation',
    'preferred_date' => '2025-08-20', // Date future
    'preferred_time' => '14:00',      // Heure de l'après-midi
    'priority' => 'normal',
    'status' => 'accepted',
    'secure_token' => \Illuminate\Support\Str::uuid(),
    'canceled_by_requester' => false,
    'ip_address' => '127.0.0.1',
    'user_agent' => 'Test Script'
]);

echo "📋 Rendez-vous créé :\n";
echo "- ID: {$appointment->id}\n";
echo "- Token: {$appointment->secure_token}\n";
echo "- Statut: {$appointment->status}\n";
echo "- Date: {$appointment->preferred_date}\n";
echo "- Heure: {$appointment->preferred_time}\n\n";

// Tester la méthode canBeCanceledByRequester
echo "🔍 Test de canBeCanceledByRequester() :\n";
try {
    $canCancel = $appointment->canBeCanceledByRequester();
    echo "- Résultat: " . ($canCancel ? '✅ Oui' : '❌ Non') . "\n";
    
    if ($canCancel) {
        echo "- ✅ Le rendez-vous peut être annulé !\n";
        echo "- URL de tracking: " . route('appointments.tracking', $appointment->secure_token) . "\n";
    } else {
        echo "- ❌ Le rendez-vous ne peut pas être annulé\n";
        $appointmentDateTime = \Carbon\Carbon::parse($appointment->preferred_date . ' ' . $appointment->preferred_time);
        $diff = now()->diffInHours($appointmentDateTime);
        echo "- Différence en heures: $diff\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de la création ===\n"; 