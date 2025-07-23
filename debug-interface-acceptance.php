<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Acceptation Interface ===\n\n";

// 1. Trouver un rendez-vous en attente
echo "🔍 Recherche d'un rendez-vous en attente...\n";
$appointment = Appointment::where('status', 'pending')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous en attente trouvé\n";
    echo "Création d'un rendez-vous de test...\n";
    
    $appointment = Appointment::create([
        'name' => 'Test Interface User',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test Interface Acceptation',
        'message' => 'Test pour debug de l\'interface',
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

// 3. Simuler l'authentification
echo "🔐 Simulation de l'authentification...\n";
auth()->login($admin);
echo "✅ Utilisateur authentifié: " . auth()->user()->name . "\n\n";

// 4. Simuler exactement le processus du contrôleur
echo "🔄 Simulation du processus du contrôleur...\n";

// Étape 1: Sauvegarder l'ancien statut (comme dans le contrôleur)
$oldStatus = $appointment->status;
echo "   📝 Ancien statut: {$oldStatus}\n";

// Étape 2: Accepter le rendez-vous (comme dans le contrôleur)
echo "   🔄 Acceptation du rendez-vous...\n";
if ($appointment->accept(auth()->user())) {
    echo "   ✅ Rendez-vous accepté via la méthode accept()\n";
    echo "   📊 Nouveau statut: {$appointment->status}\n";
    
    // Étape 3: Envoyer la notification (comme dans le contrôleur)
    echo "   📧 Envoi de la notification...\n";
    try {
        $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
        echo "   ✅ Notification envoyée avec succès !\n";
        
        // Étape 4: Log de succès (comme dans le contrôleur)
        Log::info('Email de confirmation envoyé avec succès', [
            'appointment_id' => $appointment->id,
            'recipient_email' => $appointment->email,
            'recipient_name' => $appointment->name,
            'tracking_url' => $appointment->tracking_url,
            'processed_by' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        echo "   📝 Log de succès enregistré\n";
        echo "\n🎉 Processus d'acceptation via interface simulé avec succès !\n";
        echo "📧 Email envoyé à: {$appointment->email}\n";
        echo "🔗 Lien de suivi: " . route('appointments.tracking', $appointment->secure_token) . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Erreur lors de l'envoi de la notification: " . $e->getMessage() . "\n";
        echo "   🔍 Trace: " . $e->getTraceAsString() . "\n";
        
        // Log de l'erreur (comme dans le contrôleur)
        Log::error('Erreur lors de l\'envoi de l\'email de confirmation', [
            'appointment_id' => $appointment->id,
            'recipient_email' => $appointment->email,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        echo "   📝 Log d'erreur enregistré\n";
    }
    
} else {
    echo "   ❌ Échec de l'acceptation du rendez-vous\n";
}

// 5. Vérifier les logs récents
echo "\n📝 Logs récents:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -30);
    
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || 
            strpos($line, 'confirmation') !== false || 
            strpos($line, 'notification') !== false ||
            strpos($line, 'accept') !== false ||
            strpos($line, 'interface') !== false) {
            echo "   " . $line . "\n";
        }
    }
}

echo "\n=== Test terminé ===\n"; 