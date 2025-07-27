<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic d'annulation de rendez-vous ===\n\n";

// Trouver un rendez-vous accepté
$appointment = Appointment::where('status', 'accepted')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous accepté trouvé\n";
    exit;
}

echo "📋 Rendez-vous trouvé :\n";
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
    
    if (!$canCancel) {
        $appointmentDateTime = \Carbon\Carbon::parse($appointment->preferred_date . ' ' . $appointment->preferred_time);
        $diff = now()->diffInHours($appointmentDateTime);
        echo "- Différence en heures: $diff\n";
        echo "- Le rendez-vous est trop proche de la date actuelle\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

// Tester la route directement
echo "\n🌐 Test de la route :\n";
try {
    $request = new \Illuminate\Http\Request();
    $controller = new \App\Http\Controllers\PublicController();
    $response = $controller->cancel($request, $appointment->secure_token);
    
    echo "- Type de réponse: " . get_class($response) . "\n";
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        echo "- Données: " . json_encode($data) . "\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin du diagnostic ===\n"; 