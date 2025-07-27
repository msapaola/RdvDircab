<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic du problème de refus de rendez-vous ===\n\n";

// 1. Vérifier si le rendez-vous existe
$appointmentId = 13; // ID du rendez-vous qui pose problème
$appointment = Appointment::find($appointmentId);

if (!$appointment) {
    echo "❌ Rendez-vous ID {$appointmentId} non trouvé\n";
    exit;
}

echo "✅ Rendez-vous trouvé : ID {$appointment->id}\n";
echo "   - Nom : {$appointment->name}\n";
echo "   - Email : {$appointment->email}\n";
echo "   - Statut actuel : {$appointment->status}\n";
echo "   - Date souhaitée : {$appointment->preferred_date}\n";
echo "   - Priorité : {$appointment->priority}\n\n";

// 2. Vérifier si un utilisateur admin existe
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ Aucun utilisateur admin trouvé\n";
    exit;
}

echo "✅ Utilisateur admin trouvé : {$admin->name} (ID: {$admin->id})\n\n";

// 3. Tester la méthode reject
echo "=== Test de la méthode reject ===\n";

try {
    // Sauvegarder le statut original
    $originalStatus = $appointment->status;
    
    // Tester la méthode reject
    $result = $appointment->reject($admin, 'Test de refus - Diagnostic');
    
    if ($result) {
        echo "✅ Méthode reject() exécutée avec succès\n";
        echo "   - Nouveau statut : {$appointment->status}\n";
        echo "   - Raison : {$appointment->rejection_reason}\n";
        echo "   - Traité par : {$appointment->processed_by}\n";
        echo "   - Traité le : {$appointment->processed_at}\n";
        
        // Restaurer le statut original
        $appointment->update([
            'status' => $originalStatus,
            'rejection_reason' => null,
            'processed_by' => null,
            'processed_at' => null,
        ]);
        echo "✅ Statut original restauré\n";
    } else {
        echo "❌ Méthode reject() a échoué\n";
        echo "   - Statut actuel : {$appointment->status}\n";
        echo "   - Le rendez-vous doit être en statut 'pending' pour être refusé\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de l'exécution de reject() :\n";
    echo "   - Message : {$e->getMessage()}\n";
    echo "   - Fichier : {$e->getFile()}\n";
    echo "   - Ligne : {$e->getLine()}\n";
    echo "   - Trace :\n{$e->getTraceAsString()}\n";
}

echo "\n=== Test de la notification ===\n";

try {
    // Tester l'envoi de notification
    $notification = new \App\Notifications\AppointmentStatusUpdate($appointment);
    echo "✅ Notification créée avec succès\n";
    
    // Vérifier la configuration email
    $mailConfig = config('mail');
    echo "   - Driver mail : {$mailConfig['default']}\n";
    echo "   - Host SMTP : {$mailConfig['mailers']['smtp']['host']}\n";
    echo "   - Port SMTP : {$mailConfig['mailers']['smtp']['port']}\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la création de la notification :\n";
    echo "   - Message : {$e->getMessage()}\n";
    echo "   - Fichier : {$e->getFile()}\n";
    echo "   - Ligne : {$e->getLine()}\n";
}

echo "\n=== Vérification de la base de données ===\n";

try {
    // Vérifier la structure de la table
    $columns = \DB::select("DESCRIBE appointments");
    echo "✅ Structure de la table appointments :\n";
    foreach ($columns as $column) {
        echo "   - {$column->Field} : {$column->Type} " . ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification de la base de données :\n";
    echo "   - Message : {$e->getMessage()}\n";
}

echo "\n=== Test de la route ===\n";

try {
    // Simuler une requête POST
    $request = new \Illuminate\Http\Request();
    $request->merge(['rejection_reason' => 'Test de diagnostic']);
    
    $controller = new \App\Http\Controllers\Admin\AppointmentController();
    
    // Utiliser la réflexion pour accéder à la méthode privée si nécessaire
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('reject');
    $method->setAccessible(true);
    
    echo "✅ Méthode reject accessible\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test de la route :\n";
    echo "   - Message : {$e->getMessage()}\n";
}

echo "\n=== Diagnostic terminé ===\n"; 