<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test d'annulation de rendez-vous ===\n\n";

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
echo "- Nom: {$appointment->name}\n";
echo "- Email: {$appointment->email}\n";
echo "- Date: {$appointment->preferred_date}\n";
echo "- Heure: {$appointment->preferred_time}\n\n";

// Tester la méthode canBeCanceledByRequester
echo "🔍 Test de canBeCanceledByRequester() :\n";
try {
    $canCancel = $appointment->canBeCanceledByRequester();
    echo "- Résultat: " . ($canCancel ? '✅ Oui' : '❌ Non') . "\n";
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

// Tester la méthode cancelByRequester
echo "\n🔍 Test de cancelByRequester() :\n";
try {
    $cancelled = $appointment->cancelByRequester();
    echo "- Résultat: " . ($cancelled ? '✅ Succès' : '❌ Échec') . "\n";
    echo "- Nouveau statut: {$appointment->status}\n";
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

// Tester la route
echo "\n🌐 Test de la route d'annulation :\n";
try {
    $url = "http://localhost/appointments/{$appointment->secure_token}/cancel";
    echo "- URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "- ❌ Erreur cURL: $error\n";
    } else {
        echo "- Code HTTP: $httpCode\n";
        echo "- Réponse: $response\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin du test ===\n"; 