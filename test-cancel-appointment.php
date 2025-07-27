<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Test de la méthode canBeCanceledByRequester
echo "=== Test des méthodes d'annulation ===\n";

// Récupérer un rendez-vous accepté
$appointment = Appointment::where('status', 'accepted')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous accepté trouvé\n";
    exit;
}

echo "✅ Rendez-vous trouvé : ID {$appointment->id}\n";
echo "   - Token : {$appointment->secure_token}\n";
echo "   - Statut : {$appointment->status}\n";
echo "   - Date : {$appointment->preferred_date}\n";

// Test de la méthode canBeCanceledByRequester
try {
    $canCancel = $appointment->canBeCanceledByRequester();
    echo "✅ canBeCanceledByRequester() : " . ($canCancel ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "❌ Erreur canBeCanceledByRequester() : " . $e->getMessage() . "\n";
}

// Test de l'attribut can_be_canceled_by_requester
try {
    $canCancelAttr = $appointment->can_be_canceled_by_requester;
    echo "✅ can_be_canceled_by_requester : " . ($canCancelAttr ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "❌ Erreur can_be_canceled_by_requester : " . $e->getMessage() . "\n";
}

// Test de la route
echo "\n=== Test de la route ===\n";
$url = "http://localhost/appointments/{$appointment->secure_token}/cancel";
echo "URL de test : $url\n";

// Test avec curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "Code HTTP : $httpCode\n";
if ($error) {
    echo "❌ Erreur cURL : $error\n";
} else {
    echo "✅ Réponse reçue\n";
    echo "Réponse complète :\n$response\n";
}

echo "\n=== Test terminé ===\n"; 