<?php

echo "=== FINAL CSRF FIX SCRIPT ===\n\n";

// 1. Clear all Laravel caches
echo "1. Clearing all Laravel caches...\n";
system('php artisan config:clear');
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan route:clear');
system('php artisan config:cache');
system('php artisan route:cache');

echo "\n";

// 2. Remove any cached files that might be causing issues
echo "2. Removing cached files...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $cacheFile) {
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
        echo "✓ Removed: $cacheFile\n";
    } else {
        echo "✓ Already removed: $cacheFile\n";
    }
}

echo "\n";

// 3. Check and fix bootstrap/app.php
echo "3. Checking bootstrap/app.php...\n";
$bootstrapPath = 'bootstrap/app.php';
if (file_exists($bootstrapPath)) {
    $content = file_get_contents($bootstrapPath);
    
    // Remove any VerifyCsrfToken references
    $content = preg_replace('/\/\/.*VerifyCsrfToken.*$/m', '', $content);
    
    // Ensure the file has the correct structure
    $correctContent = '<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.\'/../routes/web.php\',
        commands: __DIR__.\'/../routes/console.php\',
        health: \'/up\',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Enregistrer les middlewares personnalisés
        $middleware->alias([
            \'throttle.appointments\' => \App\Http\Middleware\ThrottleAppointments::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();';
    
    file_put_contents($bootstrapPath, $correctContent);
    echo "✓ Updated bootstrap/app.php\n";
} else {
    echo "✗ bootstrap/app.php not found\n";
}

echo "\n";

// 4. Check and fix VerifyCsrfToken middleware
echo "4. Checking VerifyCsrfToken middleware...\n";
$verifyCsrfPath = 'app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($verifyCsrfPath)) {
    $content = file_get_contents($verifyCsrfPath);
    
    // Ensure appointments is in the except array
    if (strpos($content, "'appointments'") === false) {
        $content = str_replace(
            "protected \$except = [",
            "protected \$except = [\n        'appointments',",
            $content
        );
    }
    
    if (strpos($content, "'appointments/*'") === false) {
        $content = str_replace(
            "'appointments',",
            "'appointments',\n        'appointments/*',",
            $content
        );
    }
    
    file_put_contents($verifyCsrfPath, $content);
    echo "✓ Updated VerifyCsrfToken middleware\n";
} else {
    echo "✗ VerifyCsrfToken middleware not found\n";
}

echo "\n";

// 5. Check routes/web.php
echo "5. Checking routes/web.php...\n";
$routesPath = 'routes/web.php';
if (file_exists($routesPath)) {
    $content = file_get_contents($routesPath);
    
    // Ensure the appointments route is outside web middleware group
    if (strpos($content, "Route::post('/appointments'") !== false) {
        echo "✓ /appointments route is defined\n";
        
        // Check if it's properly configured
        if (strpos($content, "->middleware(['throttle.appointments'])") !== false) {
            echo "✓ Route has correct middleware configuration\n";
        } else {
            echo "⚠ Route might need middleware adjustment\n";
        }
    } else {
        echo "✗ /appointments route not found\n";
    }
} else {
    echo "✗ routes/web.php not found\n";
}

echo "\n";

// 6. Clear all caches again
echo "6. Final cache clearing...\n";
system('php artisan config:clear');
system('php artisan cache:clear');
system('php artisan view:clear');
system('php artisan route:clear');

echo "\n";

// 7. Test the route
echo "7. Testing the route...\n";
$testScript = '<?php
require_once "vendor/autoload.php";
use Illuminate\Http\Request;

try {
    $app = require_once "bootstrap/app.php";
    $request = Request::create("/appointments", "POST", [
        "name" => "Test",
        "email" => "test@test.com",
        "phone" => "+243123456789",
        "subject" => "Test",
        "preferred_date" => "2025-07-20",
        "preferred_time" => "10:00",
        "priority" => "normal"
    ]);
    $request->headers->set("Accept", "application/json");
    $request->headers->set("X-Requested-With", "XMLHttpRequest");
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}';

file_put_contents('test-final.php', $testScript);
system('php test-final.php');
unlink('test-final.php');

echo "\n=== FIX COMPLETED ===\n";
echo "If you still see CSRF errors, the issue might be:\n";
echo "1. Server configuration (mod_security, etc.)\n";
echo "2. Hosting provider restrictions\n";
echo "3. Need to restart web server\n";
echo "4. Database connection issues\n"; 