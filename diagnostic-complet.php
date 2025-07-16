<?php

// Script de diagnostic complet pour le problème CSRF/Inertia.js
echo "=== Diagnostic Complet CSRF/Inertia.js ===\n\n";

$publicHtml = __DIR__;

echo "1. Vérification de la version des assets...\n";
$manifestPath = $publicHtml . '/public/build/manifest.json';
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if ($manifest && isset($manifest['resources/js/app.jsx'])) {
        $appHash = basename($manifest['resources/js/app.jsx']['file'], '.js');
        echo "   📦 Hash de l'app: $appHash\n";
        
        // Vérifier si c'est la dernière version
        if (strpos($appHash, 'C6d5PNWI') !== false) {
            echo "   ✅ Assets à jour (C6d5PNWI)\n";
        } else {
            echo "   ⚠️  Assets potentiellement obsolètes\n";
        }
    }
} else {
    echo "   ❌ Manifest.json non trouvé\n";
}

echo "\n2. Vérification de la configuration Inertia.js...\n";

// Vérifier le fichier app.jsx
$appJsxPath = $publicHtml . '/resources/js/app.jsx';
if (file_exists($appJsxPath)) {
    $content = file_get_contents($appJsxPath);
    if (strpos($content, '@inertiajs/react') !== false) {
        echo "   ✅ Inertia.js configuré dans app.jsx\n";
    } else {
        echo "   ❌ Inertia.js non configuré dans app.jsx\n";
    }
} else {
    echo "   ❌ app.jsx non trouvé\n";
}

echo "\n3. Vérification du middleware HandleInertiaRequests...\n";
$inertiaMiddlewarePath = $publicHtml . '/app/Http/Middleware/HandleInertiaRequests.php';
if (file_exists($inertiaMiddlewarePath)) {
    echo "   ✅ Middleware HandleInertiaRequests trouvé\n";
    $content = file_get_contents($inertiaMiddlewarePath);
    if (strpos($content, 'csrf-token') !== false) {
        echo "   ✅ CSRF token configuré dans Inertia\n";
    } else {
        echo "   ❌ CSRF token non configuré dans Inertia\n";
    }
} else {
    echo "   ❌ Middleware HandleInertiaRequests manquant\n";
}

echo "\n4. Vérification de la route appointments...\n";
$routes = system('php artisan route:list --name=appointments');
if (strpos($routes, 'appointments.store') !== false) {
    echo "   ✅ Route appointments.store trouvée\n";
    if (strpos($routes, 'withoutMiddleware') !== false) {
        echo "   ✅ CSRF désactivé pour cette route\n";
    } else {
        echo "   ⚠️  CSRF potentiellement actif\n";
    }
} else {
    echo "   ❌ Route appointments.store non trouvée\n";
}

echo "\n5. Test de la route avec différents headers...\n";

$testUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/appointments';
$testData = 'name=test&email=test@test.com&phone=123&subject=test&preferred_date=2025-07-20&preferred_time=10:00&priority=normal';

// Test 1: Avec headers Inertia.js
echo "   Test 1 - Headers Inertia.js:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest',
    'X-Inertia: true',
    'Accept: text/html, application/xhtml+xml',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "      Code HTTP: $httpCode\n";

// Test 2: Sans headers Inertia.js
echo "   Test 2 - Sans headers Inertia.js:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "      Code HTTP: $httpCode\n";

echo "\n6. Vérification des logs d'erreur...\n";
$logPath = $publicHtml . '/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lastLines = system('tail -n 20 storage/logs/laravel.log');
    echo "   📋 Dernières lignes du log:\n";
    echo "      " . str_replace("\n", "\n      ", $lastLines) . "\n";
} else {
    echo "   ❌ Fichier de log non trouvé\n";
}

echo "\n7. Vérification de la session...\n";
$sessionPath = $publicHtml . '/storage/framework/sessions';
if (is_dir($sessionPath)) {
    $sessionFiles = glob($sessionPath . '/*');
    echo "   📁 Nombre de fichiers de session: " . count($sessionFiles) . "\n";
    if (count($sessionFiles) > 0) {
        echo "   ✅ Sessions actives\n";
    } else {
        echo "   ⚠️  Aucune session active\n";
    }
} else {
    echo "   ❌ Dossier de sessions non trouvé\n";
}

echo "\n8. Vérification de la configuration de session...\n";
$configPath = $publicHtml . '/config/session.php';
if (file_exists($configPath)) {
    echo "   ✅ Configuration de session trouvée\n";
} else {
    echo "   ❌ Configuration de session manquante\n";
}

echo "\n=== Diagnostic terminé ===\n";
echo "🎯 Solutions possibles:\n";
echo "   1. Si les assets sont obsolètes: npm run build\n";
echo "   2. Si CSRF est actif: vérifiez routes/web.php\n";
echo "   3. Si Inertia.js mal configuré: vérifiez app.jsx\n";
echo "   4. Si problème de session: php artisan session:table\n\n";

echo "📝 Prochaines étapes:\n";
echo "   1. Transférez les fichiers modifiés sur le serveur\n";
echo "   2. Recompilez les assets: npm run build\n";
echo "   3. Videz les caches: php artisan optimize:clear\n";
echo "   4. Testez à nouveau le formulaire\n"; 