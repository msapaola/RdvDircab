<?php

// Script de correction finale pour le problème CSRF
echo "=== Correction Finale - Problème CSRF ===\n\n";

// 1. Nettoyer tous les caches
echo "1. Nettoyage des caches...\n";

$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
    'php artisan route:clear',
    'php artisan config:cache',
    'php artisan route:cache',
];

foreach ($commands as $command) {
    echo "   Exécution: $command\n";
    $output = shell_exec($command . ' 2>&1');
    if ($output) {
        echo "   Résultat: " . trim($output) . "\n";
    } else {
        echo "   ✅ Commande exécutée\n";
    }
}

// 2. Vérifier les permissions
echo "\n2. Vérification des permissions...\n";

$directories = [
    'storage',
    'storage/logs',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache',
    'public/build',
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "   $dir: $perms\n";
        
        if (is_writable($dir)) {
            echo "   ✅ Writable\n";
        } else {
            echo "   ❌ Non writable\n";
            // Essayer de corriger
            chmod($dir, 0755);
            echo "   🔧 Permissions corrigées\n";
        }
    } else {
        echo "   $dir: ❌ N'existe pas\n";
    }
}

// 3. Vérifier la configuration
echo "\n3. Vérification de la configuration...\n";

// Vérifier bootstrap/app.php
$bootstrapFile = 'bootstrap/app.php';
if (file_exists($bootstrapFile)) {
    $content = file_get_contents($bootstrapFile);
    if (strpos($content, 'VerifyCsrfToken::class') !== false) {
        echo "   ❌ CSRF encore présent dans bootstrap/app.php\n";
    } else {
        echo "   ✅ CSRF retiré de bootstrap/app.php\n";
    }
} else {
    echo "   ❌ bootstrap/app.php non trouvé\n";
}

// Vérifier VerifyCsrfToken.php
$csrfFile = 'app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($csrfFile)) {
    $content = file_get_contents($csrfFile);
    if (strpos($content, "'appointments'") !== false) {
        echo "   ✅ Route appointments exclue du CSRF\n";
    } else {
        echo "   ❌ Route appointments non exclue du CSRF\n";
    }
} else {
    echo "   ❌ VerifyCsrfToken.php non trouvé\n";
}

// 4. Test immédiat
echo "\n4. Test immédiat de la route...\n";

$url = 'https://green-wolverine-495039.hostingersite.com/appointments';
$testData = [
    'name' => 'Test Final',
    'email' => 'test@final.com',
    'phone' => '+243123456789',
    'subject' => 'Test final',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00',
    'priority' => 'normal',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Fix-Final/1.0');
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

echo "   Code HTTP: $httpCode\n";

$responseData = json_decode($body, true);

if ($httpCode === 419) {
    echo "   ❌ Erreur CSRF toujours présente\n";
    echo "   Le problème persiste malgré les corrections\n";
} elseif ($httpCode === 200 || $httpCode === 201) {
    echo "   ✅ SUCCÈS ! Plus d'erreur CSRF\n";
    if ($responseData && isset($responseData['success'])) {
        echo "   Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
        if (isset($responseData['message'])) {
            echo "   Message: " . $responseData['message'] . "\n";
        }
    }
} elseif ($httpCode === 422) {
    echo "   ✅ Plus d'erreur CSRF ! Erreur de validation normale\n";
    if ($responseData && isset($responseData['errors'])) {
        echo "   Erreurs de validation:\n";
        foreach ($responseData['errors'] as $field => $errors) {
            echo "     $field: " . implode(', ', $errors) . "\n";
        }
    }
} else {
    echo "   ⚠️ Code HTTP inattendu: $httpCode\n";
    echo "   Réponse: " . substr($body, 0, 500) . "\n";
}

// 5. Recommandations finales
echo "\n5. Recommandations finales:\n";

if ($httpCode === 419) {
    echo "   ❌ Le problème CSRF persiste malgré toutes les corrections.\n";
    echo "   Actions supplémentaires:\n";
    echo "   1. Redémarrez complètement le serveur web\n";
    echo "   2. Vérifiez la configuration Apache/Nginx\n";
    echo "   3. Contactez le support Hostinger\n";
    echo "   4. Considérez une solution alternative (API route)\n";
} else {
    echo "   ✅ PROBLÈME RÉSOLU !\n";
    echo "   Le formulaire devrait maintenant fonctionner correctement.\n";
    echo "   Testez-le sur le site web.\n";
}

echo "\n=== Correction terminée ===\n"; 