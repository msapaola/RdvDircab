<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Correction de tous les rendez-vous ===\n\n";

// Récupérer tous les rendez-vous
$appointments = Appointment::all();

echo "📋 Nombre de rendez-vous trouvés : " . $appointments->count() . "\n\n";

foreach ($appointments as $appointment) {
    echo "🔧 Correction du rendez-vous ID {$appointment->id} :\n";
    echo "- Avant - Date: {$appointment->preferred_date}, Heure: {$appointment->preferred_time}\n";
    
    // Extraire la date et l'heure correctes
    $dateStr = $appointment->preferred_date;
    $timeStr = $appointment->preferred_time;
    
    // Si preferred_date contient une date complète, extraire seulement la date
    if (strpos($dateStr, ' ') !== false) {
        $dateStr = explode(' ', $dateStr)[0];
    }
    
    // Si preferred_time contient une date complète, extraire seulement l'heure
    if (strpos($timeStr, ' ') !== false) {
        $timeParts = explode(' ', $timeStr);
        $timeStr = end($timeParts); // Prendre la dernière partie (l'heure)
    }
    
    // S'assurer que la date est au format Y-m-d
    try {
        $date = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
    } catch (Exception $e) {
        echo "- ❌ Erreur de parsing de date: $dateStr\n";
        continue;
    }
    
    // S'assurer que l'heure est au format H:i
    try {
        $time = \Carbon\Carbon::parse($timeStr)->format('H:i');
    } catch (Exception $e) {
        echo "- ❌ Erreur de parsing d'heure: $timeStr\n";
        continue;
    }
    
    // Mettre à jour le rendez-vous
    $appointment->update([
        'preferred_date' => $date,
        'preferred_time' => $time
    ]);
    
    echo "- Après - Date: {$appointment->preferred_date}, Heure: {$appointment->preferred_time}\n";
    echo "- ✅ Corrigé\n\n";
}

echo "=== Fin de la correction ===\n"; 