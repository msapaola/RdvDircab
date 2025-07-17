<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

echo "=== DIAGNOSTIC COMPLET DES RENDEZ-VOUS ===\n\n";

// 1. Vérifier la configuration de base
echo "1. CONFIGURATION DE BASE\n";
echo "------------------------\n";

// Vérifier les variables d'environnement
$envVars = [
    'APP_ENV',
    'APP_DEBUG',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'SESSION_DRIVER',
    'CACHE_DRIVER',
    'QUEUE_CONNECTION'
];

foreach ($envVars as $var) {
    $value = env($var);
    echo sprintf("%-20s: %s\n", $var, $value ?: 'NON DÉFINI');
}

// 2. Vérifier la base de données
echo "\n2. BASE DE DONNÉES\n";
echo "------------------\n";

try {
    $pdo = DB::connection()->getPdo();
    echo "✓ Connexion DB: OK\n";
    echo "✓ Version DB: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Vérifier les tables
    $tables = ['appointments', 'blocked_slots', 'users', 'migrations'];
    foreach ($tables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo sprintf("%-20s: %s\n", $table, $exists ? '✓ EXISTE' : '✗ MANQUANTE');
    }
    
    // Compter les rendez-vous existants
    $count = DB::table('appointments')->count();
    echo "✓ Rendez-vous existants: $count\n";
    
} catch (Exception $e) {
    echo "✗ Erreur DB: " . $e->getMessage() . "\n";
}

// 3. Vérifier les routes
echo "\n3. ROUTES\n";
echo "---------\n";

$routes = Route::getRoutes();
$appointmentRoutes = [];

foreach ($routes as $route) {
    if (str_contains($route->uri(), 'appointments')) {
        $appointmentRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'middleware' => $route->middleware()
        ];
    }
}

if (empty($appointmentRoutes)) {
    echo "✗ Aucune route de rendez-vous trouvée\n";
} else {
    foreach ($appointmentRoutes as $route) {
        echo sprintf("✓ %s %s\n", $route['method'], $route['uri']);
        echo sprintf("  Nom: %s\n", $route['name'] ?: 'Aucun');
        echo sprintf("  Middleware: %s\n", implode(', ', $route['middleware']));
        echo "\n";
    }
}

// 4. Vérifier les middlewares
echo "4. MIDDLEWARES\n";
echo "--------------\n";

$middlewares = [
    'App\Http\Middleware\VerifyCsrfToken',
    'App\Http\Middleware\ThrottleAppointments',
    'App\Http\Middleware\HandleInertiaRequests'
];

foreach ($middlewares as $middleware) {
    if (class_exists($middleware)) {
        echo "✓ $middleware: EXISTE\n";
    } else {
        echo "✗ $middleware: MANQUANT\n";
    }
}

// 5. Vérifier les modèles
echo "\n5. MODÈLES\n";
echo "----------\n";

$models = [
    'App\Models\Appointment',
    'App\Models\BlockedSlot',
    'App\Models\User'
];

foreach ($models as $model) {
    if (class_exists($model)) {
        echo "✓ $model: EXISTE\n";
        
        // Vérifier les fillables
        if (method_exists($model, 'getFillable')) {
            $instance = new $model();
            $fillables = $instance->getFillable();
            echo "  Fillables: " . implode(', ', $fillables) . "\n";
        }
    } else {
        echo "✗ $model: MANQUANT\n";
    }
}

// 6. Vérifier les migrations
echo "\n6. MIGRATIONS\n";
echo "-------------\n";

try {
    $migrations = DB::table('migrations')->get();
    $appointmentMigrations = $migrations->filter(function ($migration) {
        return str_contains($migration->migration, 'appointment') || 
               str_contains($migration->migration, 'blocked_slot');
    });
    
    if ($appointmentMigrations->isEmpty()) {
        echo "✗ Aucune migration de rendez-vous trouvée\n";
    } else {
        foreach ($appointmentMigrations as $migration) {
            echo "✓ " . $migration->migration . " (batch: " . $migration->batch . ")\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Erreur lors de la vérification des migrations: " . $e->getMessage() . "\n";
}

// 7. Vérifier les logs récents
echo "\n7. LOGS RÉCENTS\n";
echo "---------------\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -20); // 20 dernières lignes
    
    echo "Dernières 20 lignes du log:\n";
    foreach ($recentLines as $line) {
        if (str_contains($line, 'appointment') || str_contains($line, 'error') || str_contains($line, 'exception')) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo "✗ Fichier de log non trouvé\n";
}

// 8. Test de validation
echo "\n8. TEST DE VALIDATION\n";
echo "---------------------\n";

$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '+243123456789',
    'subject' => 'Test Subject',
    'message' => 'Test message',
    'preferred_date' => now()->addDays(2)->format('Y-m-d'),
    'preferred_time' => '09:00',
    'priority' => 'normal'
];

$rules = [
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255',
    'phone' => 'required|string|max:20',
    'subject' => 'required|string|max:255',
    'message' => 'nullable|string|max:1000',
    'preferred_date' => 'required|date|after:today',
    'preferred_time' => 'required|date_format:H:i',
    'priority' => 'required|in:normal,urgent,official',
];

$validator = Validator::make($testData, $rules);

if ($validator->passes()) {
    echo "✓ Validation des données: OK\n";
} else {
    echo "✗ Erreurs de validation:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
}

// 9. Vérifier les permissions de fichiers
echo "\n9. PERMISSIONS DE FICHIERS\n";
echo "--------------------------\n";

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

// 10. Vérifier la configuration CSRF
echo "\n10. CONFIGURATION CSRF\n";
echo "----------------------\n";

$csrfConfig = config('session');
echo "Session driver: " . $csrfConfig['driver'] . "\n";
echo "Session lifetime: " . $csrfConfig['lifetime'] . " minutes\n";
echo "Session domain: " . ($csrfConfig['domain'] ?: 'null') . "\n";
echo "Session secure: " . ($csrfConfig['secure'] ? 'true' : 'false') . "\n";

// 11. Test de création d'un rendez-vous
echo "\n11. TEST DE CRÉATION DE RENDEZ-VOUS\n";
echo "-----------------------------------\n";

try {
    $appointment = new \App\Models\Appointment();
    $appointment->fill($testData);
    $appointment->status = 'pending';
    $appointment->secure_token = \Illuminate\Support\Str::uuid();
    $appointment->ip_address = '127.0.0.1';
    $appointment->user_agent = 'Test Agent';
    
    if ($appointment->save()) {
        echo "✓ Création de rendez-vous: OK\n";
        echo "✓ ID: " . $appointment->id . "\n";
        echo "✓ Token: " . $appointment->secure_token . "\n";
        
        // Supprimer le test
        $appointment->delete();
        echo "✓ Test nettoyé\n";
    } else {
        echo "✗ Échec de création de rendez-vous\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors de la création: " . $e->getMessage() . "\n";
}

// 12. Vérifier les assets
echo "\n12. ASSETS\n";
echo "----------\n";

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

// 13. Vérifier les dépendances
echo "\n13. DÉPENDANCES\n";
echo "---------------\n";

$composerJson = json_decode(file_get_contents('composer.json'), true);
$requiredPackages = [
    'spatie/laravel-activitylog',
    'inertiajs/inertia-laravel'
];

foreach ($requiredPackages as $package) {
    if (isset($composerJson['require'][$package])) {
        echo "✓ $package: " . $composerJson['require'][$package] . "\n";
    } else {
        echo "✗ $package: MANQUANT\n";
    }
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
echo "\nRecommandations:\n";
echo "1. Vérifiez que tous les middlewares sont correctement enregistrés\n";
echo "2. Assurez-vous que les migrations ont été exécutées\n";
echo "3. Vérifiez les logs pour des erreurs spécifiques\n";
echo "4. Testez la soumission avec les outils de développement du navigateur\n";
echo "5. Vérifiez que les assets sont correctement compilés\n"; 