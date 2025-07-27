<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Correction robuste de tous les rendez-vous ===\n\n";

// Récupérer tous les rendez-vous
$appointments = Appointment::all();

echo "📋 Nombre de rendez-vous trouvés : " . $appointments->count() . "\n\n";

foreach ($appointments as $appointment) {
    echo "🔧 Correction du rendez-vous ID {$appointment->id} :\n";
    echo "- Avant - Date: {$appointment->preferred_date}, Heure: {$appointment->preferred_time}\n";
    
    // Extraire la date et l'heure correctes
    $dateStr = (string) $appointment->preferred_date;
    $timeStr = (string) $appointment->preferred_time;
    
    // Nettoyer et extraire la date
    $date = null;
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $dateStr, $matches)) {
        $date = $matches[1];
    } else {
        echo "- ❌ Impossible d'extraire la date de: $dateStr\n";
        continue;
    }
    
    // Nettoyer et extraire l'heure
    $time = null;
    if (preg_match('/(\d{2}:\d{2}):\d{2}/', $timeStr, $matches)) {
        $time = $matches[1];
    } elseif (preg_match('/(\d{2}:\d{2})/', $timeStr, $matches)) {
        $time = $matches[1];
    } else {
        echo "- ❌ Impossible d'extraire l'heure de: $timeStr\n";
        continue;
    }
    
    // Mettre à jour le rendez-vous avec les données nettoyées
    try {
        $appointment->update([
            'preferred_date' => $date,
            'preferred_time' => $time
        ]);
        
        // Recharger les données
        $appointment->refresh();
        
        echo "- Après - Date: {$appointment->preferred_date}, Heure: {$appointment->preferred_time}\n";
        echo "- ✅ Corrigé\n\n";
        
    } catch (Exception $e) {
        echo "- ❌ Erreur lors de la mise à jour: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Fin de la correction ===\n"; 