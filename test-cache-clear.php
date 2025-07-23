<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Cache Clear ===\n\n";

// 1. Vider tous les caches
echo "🧹 Vidage des caches...\n";

try {
    // Vider le cache de configuration
    \Artisan::call('config:clear');
    echo "✅ Cache de configuration vidé\n";
    
    // Vider le cache de routes
    \Artisan::call('route:clear');
    echo "✅ Cache de routes vidé\n";
    
    // Vider le cache de vues
    \Artisan::call('view:clear');
    echo "✅ Cache de vues vidé\n";
    
    // Vider le cache d'application
    \Artisan::call('cache:clear');
    echo "✅ Cache d'application vidé\n";
    
    // Optimiser l'application
    \Artisan::call('optimize:clear');
    echo "✅ Optimisation vidée\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du vidage des caches: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Vérifier que les modifications sont prises en compte
echo "🔍 Vérification des modifications...\n";

// Vérifier que le trait Notifiable est présent
$appointment = new \App\Models\Appointment();
$traits = class_uses($appointment);
echo "Trait Notifiable: " . (in_array('Illuminate\Notifications\Notifiable', $traits) ? '✅ Présent' : '❌ Absent') . "\n";

// Vérifier que la méthode routeNotificationForMail existe
echo "Méthode routeNotificationForMail: " . (method_exists($appointment, 'routeNotificationForMail') ? '✅ Présente' : '❌ Absente') . "\n";

// Vérifier que la notification existe
$notificationExists = class_exists('App\Notifications\AppointmentStatusUpdate');
echo "Notification AppointmentStatusUpdate: " . ($notificationExists ? '✅ Existe' : '❌ N\'existe pas') . "\n";

echo "\n";

// 3. Test rapide d'envoi d'email
echo "🧪 Test rapide d'envoi d'email...\n";

try {
    \Illuminate\Support\Facades\Mail::raw('Test après vidage de cache - ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('msapaola@gmail.com')
                ->subject('Test Cache Clear')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    });
    
    echo "✅ Email de test envoyé avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur envoi email: " . $e->getMessage() . "\n";
}

echo "\n=== Test terminé ===\n";
echo "💡 Maintenant testez l'acceptation d'un rendez-vous via l'interface\n"; 