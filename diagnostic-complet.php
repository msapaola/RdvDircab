<?php

// Script de diagnostic complet pour l'erreur CSRF
echo "=== Diagnostic Complet - Erreur CSRF ===\n\n";

// 1. Vérifier la configuration Laravel
echo "1. Configuration Laravel:\n";
echo "   APP_ENV: " . (getenv('APP_ENV') ?: 'non défini') . "\n";
echo "   APP_DEBUG: " . (getenv('APP_DEBUG') ?: 'non défini') . "\n";
echo "   APP_URL: " . (getenv('APP_URL') ?: 'non défini') . "\n";

// 2. Vérifier les middlewares actifs
echo "\n2. Middlewares actifs:\n";

// Lire le fichier bootstrap/app.php
$bootstrapFile = 'bootstrap/app.php';
if (file_exists($bootstrapFile)) {
    $content = file_get_contents($bootstrapFile);
    
    // Chercher les middlewares web
    if (preg_match_all('/web\([^)]*\)/', $content, $matches)) {
        foreach ($matches[0] as $match) {
            echo "   Middleware web: $match\n";
        }
    }
    
    // Chercher les middlewares prepend
    if (preg_match_all('/prepend:\s*\[([^\]]+)\]/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            echo "   Prepend: $match\n";
        }
    }
    
    // Chercher les middlewares append
    if (preg_match_all('/append:\s*\[([^\]]+)\]/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            echo "   Append: $match\n";
        }
    }
} else {
    echo "   ❌ Fichier bootstrap/app.php non trouvé\n";
}

// 3. Vérifier les routes
echo "\n3. Routes définies:\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    // Chercher la route appointments
    if (preg_match('/Route::post\(\'\/appointments\',\s*\[([^\]]+)\]\s*\)([^;]+);/', $content, $matches)) {
        echo "   Route /appointments trouvée:\n";
        echo "   Controller: " . $matches[1] . "\n";
        echo "   Options: " . $matches[2] . "\n";
        
        // Vérifier les middlewares
        if (strpos($matches[2], 'middleware') !== false) {
            if (preg_match('/middleware\(\[([^\]]+)\]\)/', $matches[2], $middlewareMatches)) {
                echo "   Middlewares: " . $middlewareMatches[1] . "\n";
            }
        }
        
        if (strpos($matches[2], 'withoutMiddleware') !== false) {
            echo "   ⚠️ withoutMiddleware détecté\n";
        }
    } else {
        echo "   ❌ Route /appointments non trouvée\n";
    }
} else {
    echo "   ❌ Fichier routes/web.php non trouvé\n";
}

// 4. Test de la route avec différents headers
echo "\n4. Test de la route avec différents headers:\n";

$url = 'https://green-wolverine-495039.hostingersite.com/appointments';
$testData = [
    'name' => 'Test Diagnostic',
    'email' => 'test@diagnostic.com',
    'phone' => '+243123456789',
    'subject' => 'Test diagnostic',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

// Test 1: Sans headers spéciaux
echo "   Test 1 - Sans headers spéciaux:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Diagnostic/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
curl_close($ch);

echo "     Code HTTP: $httpCode\n";
if ($httpCode === 419) {
    echo "     ❌ Erreur CSRF (419)\n";
} else {
    echo "     ✅ Pas d'erreur CSRF\n";
}

// Test 2: Avec headers AJAX
echo "   Test 2 - Avec headers AJAX:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Diagnostic/1.0');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
curl_close($ch);

echo "     Code HTTP: $httpCode\n";
if ($httpCode === 419) {
    echo "     ❌ Erreur CSRF (419)\n";
} else {
    echo "     ✅ Pas d'erreur CSRF\n";
}

// Test 3: Avec token CSRF (si disponible)
echo "   Test 3 - Avec token CSRF:\n";

// Essayer de récupérer un token CSRF
$csrfUrl = 'https://green-wolverine-495039.hostingersite.com/sanctum/csrf-cookie';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $csrfUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Diagnostic/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "     Récupération token CSRF - Code HTTP: $httpCode\n";

if ($httpCode === 204) {
    echo "     ✅ Token CSRF récupéré\n";
    
    // Maintenant tester avec le token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Diagnostic/1.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest',
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    curl_close($ch);

    echo "     Test avec token - Code HTTP: $httpCode\n";
    if ($httpCode === 419) {
        echo "     ❌ Erreur CSRF même avec token\n";
    } else {
        echo "     ✅ Pas d'erreur CSRF avec token\n";
    }
} else {
    echo "     ❌ Impossible de récupérer le token CSRF\n";
}

// 5. Vérifier les logs Laravel
echo "\n5. Logs Laravel récents:\n";
$logFiles = [
    storage_path('logs/laravel.log'),
    storage_path('logs/laravel-' . date('Y-m-d') . '.log'),
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "   📄 Log file: $logFile\n";
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -20); // 20 dernières lignes
        
        $csrfErrors = 0;
        foreach ($recentLines as $line) {
            if (strpos($line, 'CSRF') !== false || strpos($line, '419') !== false) {
                echo "     CSRF Error: " . trim($line) . "\n";
                $csrfErrors++;
            }
        }
        
        if ($csrfErrors === 0) {
            echo "     ✅ Aucune erreur CSRF dans les logs récents\n";
        }
    } else {
        echo "   ❌ Log file non trouvé: $logFile\n";
    }
}

// 6. Vérifier la configuration du middleware CSRF
echo "\n6. Configuration du middleware CSRF:\n";

$csrfMiddlewareFile = 'app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($csrfMiddlewareFile)) {
    $content = file_get_contents($csrfMiddlewareFile);
    
    // Chercher les exclusions
    if (preg_match('/protected\s+\$except\s*=\s*\[([^\]]+)\]/', $content, $matches)) {
        echo "   Exclusions CSRF: " . $matches[1] . "\n";
    } else {
        echo "   Aucune exclusion CSRF trouvée\n";
    }
} else {
    echo "   ❌ Fichier VerifyCsrfToken.php non trouvé\n";
}

// 7. Recommandations
echo "\n7. Recommandations:\n";

if ($httpCode === 419) {
    echo "   ❌ Le problème CSRF persiste\n";
    echo "   Actions recommandées:\n";
    echo "   1. Vérifiez que la route n'utilise PAS le middleware 'web'\n";
    echo "   2. Vérifiez que le middleware CSRF est bien exclu\n";
    echo "   3. Redémarrez le serveur web\n";
    echo "   4. Vérifiez la configuration Apache/Nginx\n";
} else {
    echo "   ✅ Le problème CSRF semble résolu côté backend\n";
    echo "   Le problème vient probablement du frontend\n";
    echo "   Actions recommandées:\n";
    echo "   1. Vérifiez la console du navigateur\n";
    echo "   2. Vérifiez que les assets sont bien chargés\n";
    echo "   3. Testez avec un formulaire HTML simple\n";
}

echo "\n=== Diagnostic terminé ===\n"; 