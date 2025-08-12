<?php

echo "=== Test d'acceptation de rendez-vous ===\n\n";

// Vérifier si Laravel peut démarrer
echo "🔍 Test de démarrage de Laravel...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel démarre correctement\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du démarrage de Laravel :\n";
    echo "   Message : {$e->getMessage()}\n";
    exit;
}

// Vérifier la configuration mail
echo "\n🔍 Configuration mail actuelle...\n";
try {
    $mailConfig = config('mail');
    echo "✅ Configuration mail chargée\n";
    echo "   Driver par défaut : " . ($mailConfig['default'] ?? 'non défini') . "\n";
    
    if (isset($mailConfig['mailers']['log'])) {
        echo "   Driver log disponible : Oui\n";
    } else {
        echo "   Driver log disponible : Non\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la lecture de la configuration mail :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier la base de données
echo "\n🔍 Test de la base de données...\n";
try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier s'il y a des rendez-vous en attente
    $pendingAppointments = \App\Models\Appointment::where('status', 'pending')->count();
    echo "   Rendez-vous en attente : {$pendingAppointments}\n";
    
    if ($pendingAppointments > 0) {
        $appointment = \App\Models\Appointment::where('status', 'pending')->first();
        echo "   Premier rendez-vous en attente : ID {$appointment->id} - {$appointment->name}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de base de données :\n";
    echo "   Message : {$e->getMessage()}\n";
    exit;
}

// Test de création d'un utilisateur admin simulé
echo "\n🔍 Test de création d'utilisateur admin simulé...\n";
try {
    // Créer un utilisateur admin simulé pour les tests
    $adminUser = new \App\Models\User();
    $adminUser->id = 999; // ID temporaire
    $adminUser->name = 'Admin Test';
    $adminUser->email = 'admin.test@example.com';
    $adminUser->role = 'admin';
    $adminUser->is_active = true;
    
    echo "✅ Utilisateur admin simulé créé\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la création de l'utilisateur simulé :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Test de la méthode accept du modèle Appointment
echo "\n🔍 Test de la méthode accept du modèle...\n";
try {
    if ($pendingAppointments > 0) {
        $appointment = \App\Models\Appointment::where('status', 'pending')->first();
        
        // Sauvegarder l'état initial
        $originalStatus = $appointment->status;
        $originalProcessedBy = $appointment->processed_by;
        $originalProcessedAt = $appointment->processed_at;
        
        echo "   Rendez-vous test : ID {$appointment->id}\n";
        echo "   Statut initial : {$originalStatus}\n";
        
        // Tester la méthode accept
        $result = $appointment->accept($adminUser);
        
        if ($result) {
            echo "✅ Méthode accept() réussie\n";
            echo "   Nouveau statut : {$appointment->status}\n";
            echo "   Traité par : {$appointment->processed_by}\n";
            echo "   Traité le : {$appointment->processed_at}\n";
            
            // Restaurer l'état initial
            $appointment->update([
                'status' => $originalStatus,
                'processed_by' => $originalProcessedBy,
                'processed_at' => $originalProcessedAt
            ]);
            echo "   État initial restauré\n";
            
        } else {
            echo "❌ Méthode accept() a échoué\n";
        }
        
    } else {
        echo "⚠️  Aucun rendez-vous en attente pour le test\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test de la méthode accept :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
}

// Test de la notification (sans envoi réel)
echo "\n🔍 Test de la notification (sans envoi)...\n";
try {
    if ($pendingAppointments > 0) {
        $appointment = \App\Models\Appointment::where('status', 'pending')->first();
        
        // Créer la notification
        $notification = new \App\Notifications\AppointmentStatusUpdate($appointment);
        echo "✅ Notification créée avec succès\n";
        
        // Vérifier les propriétés de la notification
        $reflection = new ReflectionClass($notification);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "   Méthodes publiques disponibles :\n";
        foreach ($methods as $method) {
            if ($method->class === get_class($notification)) {
                echo "     - {$method->name}()\n";
            }
        }
        
    } else {
        echo "⚠️  Aucun rendez-vous pour tester la notification\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test de la notification :\n";
    echo "   Message : {$e->getMessage()}\n";
    echo "   Fichier : {$e->getFile()}\n";
    echo "   Ligne : {$e->getLine()}\n";
}

echo "\n=== Test terminé ===\n";
echo "\n💡 Si tous les tests passent, l'acceptation des rendez-vous devrait fonctionner\n";
echo "💡 Les emails seront enregistrés dans storage/logs/laravel.log au lieu d'être envoyés\n"; 