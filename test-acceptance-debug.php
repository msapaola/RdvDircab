<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Debug Acceptation Rendez-vous ===\n\n";

// 1. Trouver un rendez-vous en attente
echo "🔍 Recherche d'un rendez-vous en attente...\n";
$appointment = Appointment::where('status', 'pending')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous en attente trouvé\n";
    echo "Création d'un rendez-vous de test...\n";
    
    $appointment = Appointment::create([
        'name' => 'Test Debug User',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test Debug Acceptation',
        'message' => 'Test pour debug de l\'acceptation',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    echo "✅ Rendez-vous créé (ID: {$appointment->id})\n";
} else {
    echo "✅ Rendez-vous trouvé (ID: {$appointment->id})\n";
}

// 2. Trouver un utilisateur admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit(1);
}

echo "👤 Admin: {$admin->name} ({$admin->email})\n\n";

// 3. Afficher l'état initial
echo "📋 État initial:\n";
echo "   ID: {$appointment->id}\n";
echo "   Email: {$appointment->email}\n";
echo "   Statut: {$appointment->status}\n";
echo "   Token: {$appointment->secure_token}\n\n";

// 4. Test étape par étape
echo "🔄 Test étape par étape...\n\n";

// Étape 1: Vérifier que le modèle peut recevoir des notifications
echo "Étape 1: Vérification du modèle Appointment\n";
echo "   Trait Notifiable: " . (in_array('Illuminate\Notifications\Notifiable', class_uses($appointment)) ? '✅' : '❌') . "\n";
echo "   Méthode routeNotificationForMail: " . (method_exists($appointment, 'routeNotificationForMail') ? '✅' : '❌') . "\n";

// Étape 2: Test de la méthode routeNotificationForMail
echo "\nÉtape 2: Test routeNotificationForMail\n";
try {
    $email = $appointment->routeNotificationForMail(new AppointmentStatusUpdate($appointment, 'pending'));
    echo "   ✅ Email retourné: {$email}\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Étape 3: Test de création de notification
echo "\nÉtape 3: Test création notification\n";
try {
    $notification = new AppointmentStatusUpdate($appointment, 'pending');
    echo "   ✅ Notification créée\n";
    echo "   Canaux: " . implode(', ', $notification->via($appointment)) . "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur création: " . $e->getMessage() . "\n";
}

// Étape 4: Test d'envoi de notification
echo "\nÉtape 4: Test envoi notification\n";
try {
    echo "   Envoi de la notification...\n";
    $appointment->notify(new AppointmentStatusUpdate($appointment, 'pending'));
    echo "   ✅ Notification envoyée !\n";
} catch (Exception $e) {
    echo "   ❌ Erreur envoi: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Étape 5: Test du processus complet d'acceptation
echo "\nÉtape 5: Test processus complet d'acceptation\n";
try {
    $oldStatus = $appointment->status;
    echo "   Ancien statut: {$oldStatus}\n";
    
    // Accepter le rendez-vous
    if ($appointment->accept($admin)) {
        echo "   ✅ Rendez-vous accepté\n";
        echo "   Nouveau statut: {$appointment->status}\n";
        
        // Envoyer la notification
        echo "   Envoi de la notification...\n";
        $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
        echo "   ✅ Notification envoyée après acceptation !\n";
        
        // Log de succès
        Log::info('Test debug acceptation réussi', [
            'appointment_id' => $appointment->id,
            'email' => $appointment->email,
            'admin' => $admin->name,
            'timestamp' => now()->toDateTimeString()
        ]);
        
    } else {
        echo "   ❌ Échec de l'acceptation\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur processus complet: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
    
    Log::error('Test debug acceptation échoué', [
        'appointment_id' => $appointment->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString()
    ]);
}

// 6. Vérifier les logs
echo "\n📝 Logs récents:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -20);
    
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || 
            strpos($line, 'notification') !== false || 
            strpos($line, 'accept') !== false ||
            strpos($line, 'debug') !== false) {
            echo "   " . $line . "\n";
        }
    }
}

echo "\n=== Test terminé ===\n"; 