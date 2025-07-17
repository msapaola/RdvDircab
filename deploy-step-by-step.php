<?php

echo "=== DEPLOYMENT STEP BY STEP ===\n\n";

// Step 1: Clear config cache
echo "Step 1: Clearing config cache...\n";
$output = shell_exec('php artisan config:clear 2>&1');
echo "Output: " . $output . "\n";

// Step 2: Clear application cache
echo "Step 2: Clearing application cache...\n";
$output = shell_exec('php artisan cache:clear 2>&1');
echo "Output: " . $output . "\n";

// Step 3: Clear view cache
echo "Step 3: Clearing view cache...\n";
$output = shell_exec('php artisan view:clear 2>&1');
echo "Output: " . $output . "\n";

// Step 4: Clear route cache
echo "Step 4: Clearing route cache...\n";
$output = shell_exec('php artisan route:clear 2>&1');
echo "Output: " . $output . "\n";

// Step 5: Remove cached files
echo "Step 5: Removing cached files...\n";
$files = ['bootstrap/cache/config.php', 'bootstrap/cache/routes.php', 'bootstrap/cache/packages.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Removed: $file\n";
    }
}

echo "\n=== DEPLOYMENT COMPLETED ===\n";
echo "Now test your form submission.\n";
echo "If you still get CSRF errors, try using the API route: /api/appointments\n"; 