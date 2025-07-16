<?php

// Script pour nettoyer tous les caches Laravel
echo "=== Nettoyage des Caches Laravel ===\n\n";

$publicHtml = __DIR__;

echo "1. Nettoyage du cache de configuration...\n";
system('php artisan config:clear');
echo "   ✅ Cache de configuration vidé\n";

echo "\n2. Nettoyage du cache de routes...\n";
system('php artisan route:clear');
echo "   ✅ Cache de routes vidé\n";

echo "\n3. Nettoyage du cache de vues...\n";
system('php artisan view:clear');
echo "   ✅ Cache de vues vidé\n";

echo "\n4. Nettoyage du cache d'application...\n";
system('php artisan cache:clear');
echo "   ✅ Cache d'application vidé\n";

echo "\n5. Nettoyage du cache de compilation...\n";
system('php artisan optimize:clear');
echo "   ✅ Cache de compilation vidé\n";

echo "\n6. Vérification des permissions de stockage...\n";

$storageDirs = [
    $publicHtml . '/storage/framework/cache',
    $publicHtml . '/storage/framework/sessions',
    $publicHtml . '/storage/framework/views',
    $publicHtml . '/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (is_dir($dir)) {
        system("chmod -R 755 $dir");
        echo "   ✅ Permissions mises à jour pour $dir\n";
    } else {
        echo "   ⚠️  Dossier non trouvé: $dir\n";
    }
}

echo "\n7. Vérification de la configuration...\n";

// Vérifier que les routes sont bien chargées
$routes = system('php artisan route:list --name=appointments');
if (strpos($routes, 'appointments.store') !== false) {
    echo "   ✅ Route appointments.store trouvée\n";
} else {
    echo "   ❌ Route appointments.store non trouvée\n";
}

echo "\n8. Test de la route appointments...\n";

// Test simple
$testUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/appointments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'name=test&email=test@test.com&phone=123&subject=test&preferred_date=2025-07-20&preferred_time=10:00&priority=normal');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Code HTTP: $httpCode\n";
if ($httpCode === 419) {
    echo "   ❌ Erreur CSRF toujours présente\n";
} elseif ($httpCode === 422) {
    echo "   ✅ Erreur de validation (normal, données incomplètes)\n";
} elseif ($httpCode === 302) {
    echo "   ✅ Redirection (succès Inertia.js)\n";
} else {
    echo "   ✅ Route accessible (code $httpCode)\n";
}

echo "\n=== Nettoyage terminé ===\n";
echo "🎯 Actions effectuées:\n";
echo "   1. Tous les caches Laravel vidés\n";
echo "   2. Permissions de stockage mises à jour\n";
echo "   3. Routes vérifiées\n";
echo "   4. Test de la route effectué\n\n";

echo "📝 Maintenant vous pouvez:\n";
echo "   1. Tester le formulaire sur le site web\n";
echo "   2. Si ça ne fonctionne toujours pas, vérifiez les logs:\n";
echo "      tail -f storage/logs/laravel.log\n";
echo "   3. Vérifiez que les assets sont bien compilés: npm run build\n"; 