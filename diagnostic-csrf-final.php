<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Http\Kernel;

echo "=== DIAGNOSTIC CSRF FINAL ===\n\n";

// 1. Check if VerifyCsrfToken middleware exists and is configured
echo "1. Checking VerifyCsrfToken middleware...\n";
$verifyCsrfPath = 'app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($verifyCsrfPath)) {
    echo "✓ VerifyCsrfToken middleware file exists\n";
    
    // Read the file content to check exclusions
    $content = file_get_contents($verifyCsrfPath);
    if (strpos($content, "'appointments'") !== false) {
        echo "✓ 'appointments' is in the $except array\n";
    } else {
        echo "✗ 'appointments' is NOT in the $except array\n";
    }
    
    if (strpos($content, "'appointments/*'") !== false) {
        echo "✓ 'appointments/*' is in the $except array\n";
    } else {
        echo "✗ 'appointments/*' is NOT in the $except array\n";
    }
} else {
    echo "✗ VerifyCsrfToken middleware file does not exist\n";
}

echo "\n";

// 2. Check bootstrap/app.php configuration
echo "2. Checking bootstrap/app.php...\n";
$bootstrapPath = 'bootstrap/app.php';
if (file_exists($bootstrapPath)) {
    $content = file_get_contents($bootstrapPath);
    
    if (strpos($content, 'VerifyCsrfToken') !== false) {
        echo "✗ VerifyCsrfToken is still referenced in bootstrap/app.php\n";
        // Find the line
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (strpos($line, 'VerifyCsrfToken') !== false) {
                echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "✓ VerifyCsrfToken is NOT referenced in bootstrap/app.php\n";
    }
    
    if (strpos($content, 'web(append:') !== false) {
        echo "✓ web middleware group is configured\n";
    } else {
        echo "✗ web middleware group is NOT configured\n";
    }
} else {
    echo "✗ bootstrap/app.php does not exist\n";
}

echo "\n";

// 3. Check routes/web.php
echo "3. Checking routes/web.php...\n";
$routesPath = 'routes/web.php';
if (file_exists($routesPath)) {
    $content = file_get_contents($routesPath);
    
    if (strpos($content, "Route::post('/appointments'") !== false) {
        echo "✓ /appointments POST route is defined\n";
        
        // Check if it's outside web middleware group
        $lines = explode("\n", $content);
        $inWebGroup = false;
        $appointmentsLine = -1;
        
        foreach ($lines as $i => $line) {
            if (strpos($line, 'Route::middleware(\'auth\')') !== false) {
                $inWebGroup = true;
            }
            if (strpos($line, 'Route::post(\'/appointments\'') !== false) {
                $appointmentsLine = $i;
                break;
            }
        }
        
        if ($appointmentsLine !== -1 && !$inWebGroup) {
            echo "✓ /appointments route is outside web middleware group\n";
        } else {
            echo "✗ /appointments route might be inside web middleware group\n";
        }
    } else {
        echo "✗ /appointments POST route is NOT defined\n";
    }
} else {
    echo "✗ routes/web.php does not exist\n";
}

echo "\n";

// 4. Check if there are any cached configurations
echo "4. Checking cached configurations...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $cacheFile) {
    if (file_exists($cacheFile)) {
        echo "✗ Cached file exists: $cacheFile\n";
        echo "  This might be causing the issue. Try clearing cache.\n";
    } else {
        echo "✓ No cached file: $cacheFile\n";
    }
}

echo "\n";

// 5. Check if VerifyCsrfToken is registered in the application
echo "5. Checking if VerifyCsrfToken is registered...\n";
try {
    $app = require_once 'bootstrap/app.php';
    
    // Get the HTTP kernel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Check if VerifyCsrfToken is in the middleware stack
    $middleware = $kernel->getMiddleware();
    
    $found = false;
    foreach ($middleware as $mw) {
        if (strpos($mw, 'VerifyCsrfToken') !== false) {
            echo "✗ VerifyCsrfToken found in global middleware: $mw\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "✓ VerifyCsrfToken is NOT in global middleware\n";
    }
    
    // Check web middleware group
    $webMiddleware = $kernel->getMiddlewareGroups()['web'] ?? [];
    $found = false;
    foreach ($webMiddleware as $mw) {
        if (strpos($mw, 'VerifyCsrfToken') !== false) {
            echo "✗ VerifyCsrfToken found in web middleware group: $mw\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "✓ VerifyCsrfToken is NOT in web middleware group\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error checking middleware registration: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Test the route directly
echo "6. Testing route directly...\n";
try {
    $app = require_once 'bootstrap/app.php';
    
    // Create a test request
    $request = Request::create('/appointments', 'POST', [
        'name' => 'Test',
        'email' => 'test@test.com',
        'phone' => '+243123456789',
        'subject' => 'Test',
        'preferred_date' => '2025-07-20',
        'preferred_time' => '10:00',
        'priority' => 'normal'
    ]);
    
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "✗ Error testing route: " . $e->getMessage() . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n"; 