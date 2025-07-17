<?php

echo "=== TEST CSRF SIMPLE ===\n\n";

// Test 1: Check if Laravel loads
echo "1. Testing Laravel app...\n";
try {
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app loaded\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test appointments route
echo "\n2. Testing /appointments route...\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->setUri('/appointments');
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        echo "✗ Still getting CSRF error\n";
    } else {
        echo "✓ CSRF issue resolved\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error testing route: " . $e->getMessage() . "\n";
}

// Test 3: Test API route
echo "\n3. Testing /api/appointments route...\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->setUri('/api/appointments');
    $request->headers->set('Accept', 'application/json');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 419) {
        echo "✗ API route also has CSRF error\n";
    } else {
        echo "✓ API route works\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error testing API route: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n"; 