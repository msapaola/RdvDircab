<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Support\Facades\Log;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test d'acceptation réelle de rendez-vous ===\n\n";

// 1. Trouver un rendez-vous en attente
echo "🔍 Recherche d'un rendez-vous en attente...\n";
$appointment = Appointment::where('status', 'pending')->first();

if (!$appointment) {
    echo "❌ Aucun rendez-vous en attente trouvé\n";
    echo "Création d'un rendez-vous en attente...\n";
    
    $appointment = Appointment::create([
        'name' => 'Test User Pending',
        'email' => 'msapaola@gmail.com',
        'phone' => '+243123456789',
        'subject' => 'Test Acceptation Réelle',
        'message' => 'Test pour vérifier l\'envoi d\'email lors de l\'acceptation',
        'preferred_date' => now()->addDays(7),
        'preferred_time' => '10:00',
        'priority' => 'normal',
        'status' => 'pending',
        'secure_token' => \Illuminate\Support\Str::uuid(),
    ]);
    echo "✅ Rendez-vous en attente créé (ID: {$appointment->id})\n";
} else {
    echo "✅ Rendez-vous en attente trouvé (ID: {$appointment->id})\n";
}

// 2. Trouver un utilisateur admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit(1);
}

echo "👤 Admin: {$admin->name} ({$admin->email})\n\n";

// 3. Afficher l'état initial
echo "📋 État initial du rendez-vous:\n";
echo "   ID: {$appointment->id}\n";
echo "   Nom: {$appointment->name}\n";
echo "   Email: {$appointment->email}\n";
echo "   Statut: {$appointment->status}\n";
echo "   Token: {$appointment->secure_token}\n\n";

// 4. Simuler exactement le processus d'acceptation du contrôleur
echo "🔄 Simulation du processus d'acceptation...\n";

try {
    // Étape 1: Sauvegarder l'ancien statut (comme dans le contrôleur)
    $oldStatus = $appointment->status;
    echo "   📝 Ancien statut sauvegardé: {$oldStatus}\n";
    
    // Étape 2: Accepter le rendez-vous (comme dans le modèle)
    if ($appointment->accept($admin)) {
        echo "   ✅ Rendez-vous accepté via la méthode accept()\n";
        
        // Étape 3: Envoyer la notification (comme dans le contrôleur)
        echo "   📧 Envoi de la notification...\n";
        $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
        
        echo "   ✅ Notification envoyée avec succès !\n";
        
        // Étape 4: Log de succès (comme dans le contrôleur)
        Log::info('Email de confirmation envoyé avec succès', [
            'appointment_id' => $appointment->id,
            'recipient_email' => $appointment->email,
            'recipient_name' => $appointment->name,
            'tracking_url' => $appointment->tracking_url,
            'processed_by' => $admin->name,
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        echo "   📝 Log de succès enregistré\n";
        
        // Afficher le résultat final
        echo "\n🎉 Processus d'acceptation réussi !\n";
        echo "📧 Email envoyé à: {$appointment->email}\n";
        echo "🔗 Lien de suivi: " . route('appointments.tracking', $appointment->secure_token) . "\n";
        echo "📊 Nouveau statut: {$appointment->status}\n";
        
    } else {
        echo "   ❌ Échec de l'acceptation du rendez-vous\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur lors du processus: " . $e->getMessage() . "\n";
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

// 5. Vérifier les logs récents
echo "\n📝 Logs récents pertinents:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -30);
    foreach ($recentLines as $line) {
        if (strpos($line, 'msapaola@gmail.com') !== false || 
            strpos($line, 'confirmation') !== false || 
            strpos($line, 'notification') !== false ||
            strpos($line, 'accept') !== false) {
            echo "   " . $line . "\n";
        }
    }
} else {
    echo "   Aucun fichier de log trouvé\n";
}

echo "\n=== Test terminé ===\n"; 