<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;

echo "=== TESTING SIMPLE ROUTE ===\n\n";

// Test the route directly
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
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    
    // Show the full stack trace for debugging
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== END TEST ===\n"; 