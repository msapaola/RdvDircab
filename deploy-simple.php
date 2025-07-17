<?php

echo "=== DEPLOYMENT SIMPLE ===\n\n";

// 1. Clear caches step by step
echo "1. Clearing caches...\n";
echo "  - Config cache...\n";
system('php artisan config:clear 2>&1');
echo "  - Application cache...\n";
system('php artisan cache:clear 2>&1');
echo "  - View cache...\n";
system('php artisan view:clear 2>&1');
echo "  - Route cache...\n";
system('php artisan route:clear 2>&1');

echo "✓ Caches cleared\n\n";

// 2. Remove cached files manually
echo "2. Removing cached files...\n";
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php', 
    'bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "  ✓ Removed: $file\n";
    } else {
        echo "  - Already removed: $file\n";
    }
}

echo "✓ Cached files removed\n\n";

// 3. Test if files exist
echo "3. Checking files...\n";
$files = [
    'bootstrap/app.php',
    'routes/web.php',
    'routes/api.php',
    'app/Http/Middleware/VerifyCsrfToken.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "  ✓ $file exists\n";
    } else {
        echo "  ✗ $file missing\n";
    }
}

echo "\n";

// 4. Simple test
echo "4. Testing basic functionality...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "  ✓ Laravel app loaded successfully\n";
} catch (Exception $e) {
    echo "  ✗ Error loading app: " . $e->getMessage() . "\n";
}

echo "\n=== DEPLOYMENT COMPLETED ===\n";
echo "Next steps:\n";
echo "1. Test the form submission\n";
echo "2. If still getting CSRF errors, try the API route: /api/appointments\n";
echo "3. Check server logs for any errors\n"; 