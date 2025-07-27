<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test final d'annulation ===\n\n";

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
        echo "- ❌ Le rendez-vous ne peut pas être annulé\n";
        echo "- Raison: Probablement trop proche de la date\n";
        exit;
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
    exit;
}

// Tester l'annulation directe
echo "\n🔍 Test d'annulation directe :\n";
try {
    $cancelled = $appointment->cancelByRequester();
    echo "- Résultat: " . ($cancelled ? '✅ Succès' : '❌ Échec') . "\n";
    echo "- Nouveau statut: {$appointment->status}\n";
    
    if ($cancelled) {
        echo "- ✅ L'annulation fonctionne correctement !\n";
    } else {
        echo "- ❌ L'annulation a échoué\n";
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

// Tester la route via le contrôleur
echo "\n🌐 Test via le contrôleur :\n";
try {
    $request = new \Illuminate\Http\Request();
    $controller = new \App\Http\Controllers\PublicController();
    $response = $controller->cancel($request, $appointment->secure_token);
    
    echo "- Type de réponse: " . get_class($response) . "\n";
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        echo "- Données: " . json_encode($data) . "\n";
        
        if (isset($data->success) && $data->success) {
            echo "- ✅ La route fonctionne correctement !\n";
        } else {
            echo "- ❌ La route a retourné une erreur\n";
        }
    }
} catch (Exception $e) {
    echo "- ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Fin du test ===\n"; 