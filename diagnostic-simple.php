<?php

// Script de diagnostic simple pour les rendez-vous
echo "=== DIAGNOSTIC SIMPLE DES RENDEZ-VOUS ===\n\n";

// 1. Vérifier les fichiers essentiels
echo "1. FICHIERS ESSENTIELS\n";
echo "----------------------\n";

$files = [
    'app/Http/Controllers/PublicController.php',
    'app/Models/Appointment.php',
    'app/Http/Middleware/ThrottleAppointments.php',
    'resources/js/Pages/Public/Home.jsx',
    'resources/js/Components/Forms/AppointmentForm.jsx',
    'routes/web.php',
    '.env'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file: EXISTE\n";
    } else {
        echo "✗ $file: MANQUANT\n";
    }
}

// 2. Vérifier le contenu du fichier .env
echo "\n2. CONFIGURATION ENVIRONNEMENT\n";
echo "------------------------------\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $envLines = explode("\n", $envContent);
    
    $importantVars = [
        'APP_ENV',
        'APP_DEBUG',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD'
    ];
    
    foreach ($importantVars as $var) {
        $found = false;
        foreach ($envLines as $line) {
            if (strpos($line, $var . '=') === 0) {
                $value = trim(substr($line, strlen($var) + 1));
                echo sprintf("%-20s: %s\n", $var, $value ?: 'VIDE');
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo sprintf("%-20s: %s\n", $var, 'NON DÉFINI');
        }
    }
} else {
    echo "✗ Fichier .env manquant\n";
}

// 3. Vérifier les logs récents
echo "\n3. LOGS RÉCENTS\n";
echo "---------------\n";

$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -10); // 10 dernières lignes
    
    echo "Dernières 10 lignes du log:\n";
    foreach ($recentLines as $line) {
        if (str_contains($line, 'appointment') || str_contains($line, 'error') || str_contains($line, 'exception') || str_contains($line, '419')) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo "✗ Fichier de log non trouvé\n";
}

// 4. Vérifier les permissions
echo "\n4. PERMISSIONS\n";
echo "--------------\n";

$paths = [
    'storage/app/public',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'bootstrap/cache'
];

foreach ($paths as $path) {
    if (is_dir($path)) {
        $writable = is_writable($path);
        echo sprintf("%-30s: %s\n", $path, $writable ? '✓ ÉCRITURE' : '✗ LECTURE SEULE');
    } else {
        echo sprintf("%-30s: %s\n", $path, '✗ N\'EXISTE PAS');
    }
}

// 5. Vérifier les assets
echo "\n5. ASSETS\n";
echo "---------\n";

$assetPaths = [
    'public/build/manifest.json',
    'public/build/assets',
    'resources/js/Pages/Public/Home.jsx',
    'resources/js/Components/Forms/AppointmentForm.jsx'
];

foreach ($assetPaths as $path) {
    if (file_exists($path)) {
        echo "✓ $path: EXISTE\n";
    } else {
        echo "✗ $path: MANQUANT\n";
    }
}

// 6. Analyser le code JavaScript
echo "\n6. ANALYSE DU CODE JAVASCRIPT\n";
echo "-----------------------------\n";

// Vérifier le composant Home.jsx
if (file_exists('resources/js/Pages/Public/Home.jsx')) {
    $homeContent = file_get_contents('resources/js/Pages/Public/Home.jsx');
    
    // Vérifier les imports
    if (str_contains($homeContent, 'import React')) {
        echo "✓ Import React: OK\n";
    } else {
        echo "✗ Import React: MANQUANT\n";
    }
    
    if (str_contains($homeContent, 'handleSubmit')) {
        echo "✓ Fonction handleSubmit: PRÉSENTE\n";
    } else {
        echo "✗ Fonction handleSubmit: MANQUANTE\n";
    }
    
    if (str_contains($homeContent, 'fetch(\'/appointments\'')) {
        echo "✓ Appel fetch /appointments: PRÉSENT\n";
    } else {
        echo "✗ Appel fetch /appointments: MANQUANT\n";
    }
    
    if (str_contains($homeContent, 'isSubmitting')) {
        echo "✓ État isSubmitting: PRÉSENT\n";
    } else {
        echo "✗ État isSubmitting: MANQUANT\n";
    }
}

// 7. Analyser le contrôleur
echo "\n7. ANALYSE DU CONTRÔLEUR\n";
echo "------------------------\n";

if (file_exists('app/Http/Controllers/PublicController.php')) {
    $controllerContent = file_get_contents('app/Http/Controllers/PublicController.php');
    
    if (str_contains($controllerContent, 'public function store')) {
        echo "✓ Méthode store: PRÉSENTE\n";
    } else {
        echo "✗ Méthode store: MANQUANTE\n";
    }
    
    if (str_contains($controllerContent, 'Validator::make')) {
        echo "✓ Validation: PRÉSENTE\n";
    } else {
        echo "✗ Validation: MANQUANTE\n";
    }
    
    if (str_contains($controllerContent, 'Appointment::create')) {
        echo "✓ Création de rendez-vous: PRÉSENTE\n";
    } else {
        echo "✗ Création de rendez-vous: MANQUANTE\n";
    }
    
    if (str_contains($controllerContent, 'response()->json')) {
        echo "✓ Réponse JSON: PRÉSENTE\n";
    } else {
        echo "✗ Réponse JSON: MANQUANTE\n";
    }
}

// 8. Analyser les routes
echo "\n8. ANALYSE DES ROUTES\n";
echo "--------------------\n";

if (file_exists('routes/web.php')) {
    $routesContent = file_get_contents('routes/web.php');
    
    if (str_contains($routesContent, 'Route::post(\'/appointments\'')) {
        echo "✓ Route POST /appointments: PRÉSENTE\n";
    } else {
        echo "✗ Route POST /appointments: MANQUANTE\n";
    }
    
    if (str_contains($routesContent, 'withoutMiddleware([\\App\\Http\\Middleware\\VerifyCsrfToken::class])')) {
        echo "✓ CSRF désactivé: OUI\n";
    } else {
        echo "✗ CSRF désactivé: NON\n";
    }
    
    if (str_contains($routesContent, 'throttle.appointments')) {
        echo "✓ Middleware throttle.appointments: PRÉSENT\n";
    } else {
        echo "✗ Middleware throttle.appointments: MANQUANT\n";
    }
}

// 9. Vérifier le modèle Appointment
echo "\n9. ANALYSE DU MODÈLE\n";
echo "--------------------\n";

if (file_exists('app/Models/Appointment.php')) {
    $modelContent = file_get_contents('app/Models/Appointment.php');
    
    if (str_contains($modelContent, 'protected $fillable')) {
        echo "✓ Propriété fillable: PRÉSENTE\n";
    } else {
        echo "✗ Propriété fillable: MANQUANTE\n";
    }
    
    if (str_contains($modelContent, 'const STATUS_PENDING')) {
        echo "✓ Constantes de statut: PRÉSENTES\n";
    } else {
        echo "✗ Constantes de statut: MANQUANTES\n";
    }
    
    if (str_contains($modelContent, 'secure_token')) {
        echo "✓ Champ secure_token: PRÉSENT\n";
    } else {
        echo "✗ Champ secure_token: MANQUANT\n";
    }
}

echo "\n=== RECOMMANDATIONS ===\n";
echo "1. Exécutez: php artisan migrate:status\n";
echo "2. Exécutez: php artisan route:list | grep appointment\n";
echo "3. Exécutez: php artisan config:clear && php artisan cache:clear\n";
echo "4. Vérifiez les outils de développement du navigateur (Console, Network)\n";
echo "5. Testez avec: curl -X POST http://localhost/appointments -H 'Content-Type: application/json' -d '{\"name\":\"test\",\"email\":\"test@test.com\",\"phone\":\"123\",\"subject\":\"test\",\"preferred_date\":\"2024-12-25\",\"preferred_time\":\"09:00\",\"priority\":\"normal\"}'\n"; 