<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Correction du rendez-vous de test ===\n\n";

// Trouver le rendez-vous de test
$appointment = Appointment::find(1);

if (!$appointment) {
    echo "❌ Rendez-vous ID 1 non trouvé\n";
    exit;
}

echo "📋 Rendez-vous avant correction :\n";
echo "- ID: {$appointment->id}\n";
echo "- Date: {$appointment->preferred_date}\n";
echo "- Heure: {$appointment->preferred_time}\n";
echo "- Statut: {$appointment->status}\n\n";

// Corriger les données pour un test futur
$appointment->update([
    'preferred_date' => '2025-08-15', // Date future (15 août 2025)
    'preferred_time' => '14:00',      // Heure de l'après-midi
    'status' => 'accepted'            // S'assurer qu'il est accepté
]);

echo "📋 Rendez-vous après correction :\n";
echo "- ID: {$appointment->id}\n";
echo "- Date: {$appointment->preferred_date}\n";
echo "- Heure: {$appointment->preferred_time}\n";
echo "- Statut: {$appointment->status}\n\n";

// Tester la méthode canBeCanceledByRequester
echo "🔍 Test de canBeCanceledByRequester() :\n";
try {
    $canCancel = $appointment->canBeCanceledByRequester();
    echo "- Résultat: " . ($canCancel ? '✅ Oui' : '❌ Non') . "\n";
    
    if ($canCancel) {
        echo "- ✅ Le rendez-vous peut maintenant être annulé !\n";
        echo "- Token pour test: {$appointment->secure_token}\n";
    } else {
        echo "- ❌ Le rendez-vous ne peut toujours pas être annulé\n";
        $appointmentDateTime = \Carbon\Carbon::parse($appointment->preferred_date . ' ' . $appointment->preferred_time);
        $diff = now()->diffInHours($appointmentDateTime);
        echo "- Différence en heures: $diff\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de la correction ===\n"; 