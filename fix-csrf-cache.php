<?php

// Script pour nettoyer les caches après correction CSRF
echo "=== Nettoyage des caches après correction CSRF ===\n\n";

// 1. Nettoyer les caches Laravel
echo "1. Nettoyage des caches Laravel...\n";

$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
    'php artisan route:clear',
    'php artisan config:cache',
    'php artisan route:cache',
];

foreach ($commands as $command) {
    echo "   Exécution: $command\n";
    $output = shell_exec($command . ' 2>&1');
    if ($output) {
        echo "   Résultat: " . trim($output) . "\n";
    } else {
        echo "   ✅ Commande exécutée\n";
    }
}

// 2. Vérifier les permissions
echo "\n2. Vérification des permissions...\n";

$directories = [
    'storage',
    'storage/logs',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache',
    'public/build',
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "   $dir: $perms\n";
        
        // Vérifier si c'est writable
        if (is_writable($dir)) {
            echo "   ✅ Writable\n";
        } else {
            echo "   ❌ Non writable\n";
        }
    } else {
        echo "   $dir: ❌ N'existe pas\n";
    }
}

// 3. Vérifier la configuration des routes
echo "\n3. Vérification de la configuration des routes...\n";

// Vérifier que le fichier de routes est correct
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    if (strpos($content, 'middleware([\'throttle.appointments\'])') !== false) {
        echo "   ✅ Route /appointments correctement configurée\n";
    } else {
        echo "   ❌ Route /appointments mal configurée\n";
    }
    
    if (strpos($content, 'withoutMiddleware') !== false) {
        echo "   ⚠️ Ancienne configuration CSRF détectée\n";
    } else {
        echo "   ✅ Aucune ancienne configuration CSRF\n";
    }
} else {
    echo "   ❌ Fichier routes/web.php non trouvé\n";
}

// 4. Vérifier le middleware ThrottleAppointments
echo "\n4. Vérification du middleware ThrottleAppointments...\n";

$middlewareFile = 'app/Http/Middleware/ThrottleAppointments.php';
if (file_exists($middlewareFile)) {
    echo "   ✅ Middleware ThrottleAppointments existe\n";
    
    $content = file_get_contents($middlewareFile);
    if (strpos($content, 'RateLimiter::tooManyAttempts') !== false) {
        echo "   ✅ Rate limiting configuré\n";
    } else {
        echo "   ❌ Rate limiting mal configuré\n";
    }
} else {
    echo "   ❌ Middleware ThrottleAppointments manquant\n";
}

// 5. Vérifier la configuration bootstrap/app.php
echo "\n5. Vérification de la configuration bootstrap/app.php...\n";

$bootstrapFile = 'bootstrap/app.php';
if (file_exists($bootstrapFile)) {
    $content = file_get_contents($bootstrapFile);
    if (strpos($content, 'throttle.appointments') !== false) {
        echo "   ✅ Middleware alias configuré\n";
    } else {
        echo "   ❌ Middleware alias manquant\n";
    }
} else {
    echo "   ❌ Fichier bootstrap/app.php non trouvé\n";
}

// 6. Test rapide de la route
echo "\n6. Test rapide de la route...\n";

$testUrl = 'https://green-wolverine-495039.hostingersite.com/appointments';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'test=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Cache-Clear-Test/1.0');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";

if ($httpCode === 419) {
    echo "   ❌ Erreur CSRF toujours présente\n";
} elseif ($httpCode === 422 || $httpCode === 200) {
    echo "   ✅ Plus d'erreur CSRF !\n";
} else {
    echo "   ⚠️ Code HTTP inattendu\n";
}

echo "\n=== Nettoyage terminé ===\n";
echo "\nActions recommandées:\n";
echo "1. Testez le formulaire sur le site web\n";
echo "2. Vérifiez la console du navigateur\n";
echo "3. Vérifiez les logs Laravel si nécessaire\n";
echo "4. Si le problème persiste, redémarrez le serveur web\n"; 