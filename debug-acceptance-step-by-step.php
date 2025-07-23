<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Acceptation Étape par Étape ===\n\n";

// 1. Créer un rendez-vous de test
echo "📝 Création d'un rendez-vous de test...\n";
$appointment = Appointment::create([
    'name' => 'Test Step by Step',
    'email' => 'msapaola@gmail.com',
    'phone' => '+243123456789',
    'subject' => 'Test Step by Step',
    'message' => 'Test pour debug étape par étape',
    'preferred_date' => now()->addDays(7),
    'preferred_time' => '10:00',
    'priority' => 'normal',
    'status' => 'pending',
    'secure_token' => \Illuminate\Support\Str::uuid(),
]);

echo "✅ Rendez-vous créé (ID: {$appointment->id}, Statut: {$appointment->status})\n\n";

// 2. Trouver un utilisateur admin
echo "👤 Recherche d'un utilisateur admin...\n";
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit(1);
}
echo "✅ Admin trouvé: {$admin->name} ({$admin->email})\n\n";

// 3. Test d'authentification
echo "🔐 Test d'authentification...\n";
try {
    auth()->login($admin);
    $currentUser = auth()->user();
    echo "✅ Authentification réussie: {$currentUser->name}\n";
} catch (Exception $e) {
    echo "❌ Erreur d'authentification: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 4. Test de la méthode accept() du modèle
echo "🔄 Test de la méthode accept() du modèle...\n";
try {
    $oldStatus = $appointment->status;
    echo "   📝 Ancien statut: {$oldStatus}\n";
    
    $result = $appointment->accept($admin);
    echo "   📊 Résultat de accept(): " . ($result ? 'true' : 'false') . "\n";
    echo "   📊 Nouveau statut: {$appointment->status}\n";
    
    if ($result) {
        echo "   ✅ Méthode accept() réussie\n";
    } else {
        echo "   ❌ Méthode accept() échouée\n";
        echo "   🔍 Vérification des conditions...\n";
        echo "   - Statut actuel: {$appointment->status}\n";
        echo "   - Statut requis: pending\n";
        echo "   - Condition: " . ($appointment->status === 'pending' ? '✅ OK' : '❌ NOK') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur dans accept(): " . $e->getMessage() . "\n";
    echo "   🔍 Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// 5. Test de création de notification
echo "📧 Test de création de notification...\n";
try {
    $notification = new AppointmentStatusUpdate($appointment, $oldStatus);
    echo "   ✅ Notification créée\n";
    echo "   📋 Classe: " . get_class($notification) . "\n";
    echo "   📋 Canaux: " . implode(', ', $notification->via($appointment)) . "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur création notification: " . $e->getMessage() . "\n";
    echo "   🔍 Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// 6. Test de la méthode routeNotificationForMail
echo "📮 Test de routeNotificationForMail...\n";
try {
    $email = $appointment->routeNotificationForMail($notification);
    echo "   ✅ Email retourné: {$email}\n";
} catch (Exception $e) {
    echo "   ❌ Erreur routeNotificationForMail: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. Test d'envoi de notification
echo "📤 Test d'envoi de notification...\n";
try {
    echo "   🔄 Envoi en cours...\n";
    $appointment->notify($notification);
    echo "   ✅ Notification envoyée avec succès !\n";
    
    // Log de succès
    Log::info('Test étape par étape réussi', [
        'appointment_id' => $appointment->id,
        'email' => $appointment->email,
        'admin' => $admin->name,
        'timestamp' => now()->toDateTimeString()
    ]);
    
} catch (Exception $e) {
    echo "   ❌ Erreur envoi notification: " . $e->getMessage() . "\n";
    echo "   🔍 Trace: " . $e->getTraceAsString() . "\n";
    
    Log::error('Test étape par étape échoué', [
        'appointment_id' => $appointment->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'timestamp' => now()->toDateTimeString()
    ]);
}

echo "\n";

// 8. Test du processus complet (comme dans le contrôleur)
echo "🎯 Test du processus complet (comme dans le contrôleur)...\n";
try {
    // Recréer un rendez-vous pour le test complet
    $appointment2 = Appointment::create([
        'name' => 'Test Complet',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test Complet',
        'message' => 'Test du processus complet',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    
    echo "   📝 Rendez-vous de test créé (ID: {$appointment2->id})\n";
    
    $oldStatus2 = $appointment2->status;
    echo "   📝 Ancien statut: {$oldStatus2}\n";
    
    if ($appointment2->accept(auth()->user())) {
        echo "   ✅ Rendez-vous accepté\n";
        
        try {
            $appointment2->notify(new AppointmentStatusUpdate($appointment2, $oldStatus2));
            echo "   ✅ Notification envoyée dans le processus complet !\n";
            
            Log::info('Processus complet réussi', [
                'appointment_id' => $appointment2->id,
                'email' => $appointment2->email,
                'admin' => auth()->user()->name,
                'timestamp' => now()->toDateTimeString()
            ]);
            
        } catch (Exception $e) {
            echo "   ❌ Erreur notification dans processus complet: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ❌ Échec de l'acceptation dans le processus complet\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur processus complet: " . $e->getMessage() . "\n";
}

echo "\n";

// 9. Vérifier les logs
echo "📝 Logs récents:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -20);
    
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || 
            strpos($line, 'notification') !== false || 
            strpos($line, 'accept') !== false ||
            strpos($line, 'étape') !== false ||
            strpos($line, 'complet') !== false) {
            echo "   " . $line . "\n";
        }
    }
}

echo "\n=== Test terminé ===\n"; 