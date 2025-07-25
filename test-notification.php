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
    echo "   - Driver: " . ($mailConfig['default'] ?? 'non défini') . "\n";
    
    if (isset($mailConfig['mailers']['smtp'])) {
        $smtpConfig = $mailConfig['mailers']['smtp'];
        echo "   - Host: " . ($smtpConfig['host'] ?? 'non défini') . "\n";
        echo "   - Port: " . ($smtpConfig['port'] ?? 'non défini') . "\n";
        echo "   - Encryption: " . ($smtpConfig['encryption'] ?? 'non défini') . "\n";
        echo "   - Username: " . ($smtpConfig['username'] ?? 'non défini') . "\n";
        echo "   - Password: " . (isset($smtpConfig['password']) ? '***défini***' : 'non défini') . "\n";
    } else {
        echo "   - Configuration SMTP non trouvée\n";
    }
    
    if (isset($mailConfig['from'])) {
        echo "   - From Address: " . ($mailConfig['from']['address'] ?? 'non défini') . "\n";
        echo "   - From Name: " . ($mailConfig['from']['name'] ?? 'non défini') . "\n";
    }
    echo "\n";

    // 2. Test de connexion SMTP simple
    echo "2. Test de connexion SMTP...\n";
    if (isset($mailConfig['mailers']['smtp'])) {
        $host = $mailConfig['mailers']['smtp']['host'] ?? '';
        $port = $mailConfig['mailers']['smtp']['port'] ?? 25;
        
        if ($host) {
            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            if ($connection) {
                echo "   ✓ Connexion réussie à $host:$port\n";
                fclose($connection);
            } else {
                echo "   ✗ Échec de connexion à $host:$port - $errstr ($errno)\n";
            }
        } else {
            echo "   ✗ Host SMTP non défini\n";
        }
    } else {
        echo "   ✗ Configuration SMTP non disponible\n";
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

    // 5. Vérification du fichier .env
    echo "5. Vérification des variables d'environnement...\n";
    $envFile = '.env';
    if (file_exists($envFile)) {
        echo "   ✓ Fichier .env trouvé\n";
        $envContent = file_get_contents($envFile);
        
        $mailVars = [
            'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 
            'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'
        ];
        
        foreach ($mailVars as $var) {
            if (preg_match("/^$var=(.+)$/m", $envContent, $matches)) {
                $value = trim($matches[1]);
                if ($var === 'MAIL_PASSWORD') {
                    echo "   - $var: " . (strlen($value) > 0 ? '***défini***' : 'non défini') . "\n";
                } else {
                    echo "   - $var: $value\n";
                }
            } else {
                echo "   - $var: non défini\n";
            }
        }
    } else {
        echo "   ✗ Fichier .env non trouvé\n";
    }
    echo "\n";

    // 6. Suggestions de solutions
    echo "6. Suggestions de solutions:\n";
    echo "   a) Configuration SMTP recommandée dans .env:\n";
    echo "      MAIL_MAILER=smtp\n";
    echo "      MAIL_HOST=gouv.kinshasa.cd\n";
    echo "      MAIL_PORT=465\n";
    echo "      MAIL_USERNAME=votre_username\n";
    echo "      MAIL_PASSWORD=votre_password\n";
    echo "      MAIL_ENCRYPTION=ssl\n";
    echo "      MAIL_FROM_ADDRESS=votre_email@gouv.kinshasa.cd\n";
    echo "      MAIL_FROM_NAME=\"Cabinet du Gouverneur\"\n\n";
    
    echo "   b) Alternative avec port 587:\n";
    echo "      MAIL_PORT=587\n";
    echo "      MAIL_ENCRYPTION=tls\n\n";
    
    echo "   c) Test avec Gmail (temporaire):\n";
    echo "      MAIL_HOST=smtp.gmail.com\n";
    echo "      MAIL_PORT=587\n";
    echo "      MAIL_ENCRYPTION=tls\n\n";
    
    echo "   d) Vérifier que le port 465 n'est pas bloqué par le firewall\n";
    echo "   e) Les notifications sont maintenant gérées avec try-catch\n\n";

    // 7. Test avec try-catch pour l'envoi réel
    echo "7. Test d'envoi réel (optionnel)...\n";
    echo "   Voulez-vous tester l'envoi réel ? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) === 'y') {
        try {
            \Illuminate\Support\Facades\Notification::route('mail', 'msapaola@gmail.com')
                ->notify($notification);
            echo "   ✓ Notification envoyée avec succès à msapaola@gmail.com\n";
        } catch (Exception $e) {
            echo "   ✗ Erreur d'envoi: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Erreur générale: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n"; 