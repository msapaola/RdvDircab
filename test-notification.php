<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Notifications\AppointmentStatusUpdate;
use App\Models\Appointment;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test des Notifications ===\n\n";

try {
    // 1. Test de la configuration mail
    echo "1. Vérification de la configuration mail...\n";
    $mailConfig = config('mail');
    echo "   - Driver: " . $mailConfig['default'] . "\n";
    echo "   - Host: " . $mailConfig['mailers']['smtp']['host'] . "\n";
    echo "   - Port: " . $mailConfig['mailers']['smtp']['port'] . "\n";
    echo "   - Encryption: " . $mailConfig['mailers']['smtp']['encryption'] . "\n";
    echo "   - Username: " . $mailConfig['mailers']['smtp']['username'] . "\n";
    echo "   - From: " . $mailConfig['from']['address'] . "\n\n";

    // 2. Test de connexion SMTP simple
    echo "2. Test de connexion SMTP...\n";
    $host = $mailConfig['mailers']['smtp']['host'];
    $port = $mailConfig['mailers']['smtp']['port'];
    
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($connection) {
        echo "   ✓ Connexion réussie à $host:$port\n";
        fclose($connection);
    } else {
        echo "   ✗ Échec de connexion à $host:$port - $errstr ($errno)\n";
    }
    echo "\n";

    // 3. Test avec un rendez-vous factice
    echo "3. Test de création de notification...\n";
    
    // Créer un rendez-vous factice pour le test
    $testAppointment = new Appointment();
    $testAppointment->id = 999;
    $testAppointment->name = 'Test User';
    $testAppointment->email = 'test@example.com';
    $testAppointment->subject = 'Test Notification';
    $testAppointment->preferred_date = '2025-07-30';
    $testAppointment->preferred_time = '10:00';
    $testAppointment->status = 'accepted';
    $testAppointment->formatted_status = 'Accepté';
    $testAppointment->formatted_priority = 'Normal';
    
    echo "   ✓ Rendez-vous de test créé\n";

    // 4. Test de création de notification (sans envoi)
    echo "4. Test de création de notification...\n";
    try {
        $notification = new AppointmentStatusUpdate($testAppointment);
        echo "   ✓ Notification créée avec succès\n";
        
        // Test de la méthode toMail (sans envoi réel)
        $mailMessage = $notification->toMail($testAppointment);
        echo "   ✓ Méthode toMail exécutée avec succès\n";
        
    } catch (Exception $e) {
        echo "   ✗ Erreur lors de la création de notification: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Suggestions de solutions
    echo "5. Suggestions de solutions:\n";
    echo "   a) Vérifier la configuration SMTP dans .env:\n";
    echo "      MAIL_MAILER=smtp\n";
    echo "      MAIL_HOST=gouv.kinshasa.cd\n";
    echo "      MAIL_PORT=465\n";
    echo "      MAIL_USERNAME=votre_username\n";
    echo "      MAIL_PASSWORD=votre_password\n";
    echo "      MAIL_ENCRYPTION=ssl\n";
    echo "      MAIL_FROM_ADDRESS=votre_email@gouv.kinshasa.cd\n";
    echo "      MAIL_FROM_NAME=\"Cabinet du Gouverneur\"\n\n";
    
    echo "   b) Tester avec un service SMTP alternatif (Gmail, Mailgun, etc.)\n";
    echo "   c) Vérifier que le port 465 n'est pas bloqué par le firewall\n";
    echo "   d) Utiliser le port 587 avec TLS au lieu de 465 avec SSL\n";
    echo "   e) Désactiver temporairement les notifications pour tester:\n";
    echo "      - Commenter la ligne d'envoi de notification dans AppointmentController\n\n";

    // 6. Test avec try-catch pour l'envoi réel
    echo "6. Test d'envoi réel (optionnel)...\n";
    echo "   Voulez-vous tester l'envoi réel ? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) === 'y') {
        try {
            \Illuminate\Support\Facades\Notification::route('mail', 'test@example.com')
                ->notify($notification);
            echo "   ✓ Notification envoyée avec succès\n";
        } catch (Exception $e) {
            echo "   ✗ Erreur d'envoi: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Erreur générale: " . $e->getMessage() . "\n";
}

echo "\n=== Fin du test ===\n"; 