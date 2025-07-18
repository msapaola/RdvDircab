<?php

echo "=== CORRECTION PAGE LOGIN ===\n\n";

// 1. Vérifier et corriger les assets
echo "1. Vérification des assets...\n";

// Vérifier si le manifest existe
if (!file_exists('public/build/manifest.json')) {
    echo "⚠ Manifest manquant - Compilation des assets nécessaire\n";
    echo "Exécution de npm run build...\n";
    system('npm run build 2>&1');
} else {
    echo "✓ Manifest existe\n";
}

// 2. Vérifier la configuration Inertia
echo "\n2. Vérification de la configuration Inertia...\n";
$appFile = 'resources/js/app.jsx';
if (file_exists($appFile)) {
    $content = file_get_contents($appFile);
    if (strpos($content, 'createInertiaApp') !== false) {
        echo "✓ Configuration Inertia détectée\n";
    } else {
        echo "⚠ Configuration Inertia manquante\n";
    }
} else {
    echo "✗ app.jsx manquant\n";
}

// 3. Vérifier le layout principal
echo "\n3. Vérification du layout...\n";
$layoutFile = 'resources/views/app.blade.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);
    if (strpos($content, '@inertiaHead') !== false && strpos($content, '@inertia') !== false) {
        echo "✓ Layout Inertia configuré\n";
    } else {
        echo "⚠ Layout Inertia mal configuré\n";
    }
} else {
    echo "✗ Layout app.blade.php manquant\n";
}

// 4. Vérifier la page Login.jsx
echo "\n4. Vérification de la page Login.jsx...\n";
$loginFile = 'resources/js/Pages/Auth/Login.jsx';
if (file_exists($loginFile)) {
    $content = file_get_contents($loginFile);
    
    // Vérifier les imports
    if (strpos($content, 'import') !== false) {
        echo "✓ Imports détectés\n";
    } else {
        echo "⚠ Aucun import détecté\n";
    }
    
    // Vérifier l'export
    if (strpos($content, 'export default') !== false) {
        echo "✓ Export default détecté\n";
    } else {
        echo "⚠ Export default manquant\n";
    }
    
    // Vérifier les composants
    if (strpos($content, 'useForm') !== false) {
        echo "✓ useForm détecté\n";
    } else {
        echo "⚠ useForm manquant\n";
    }
} else {
    echo "✗ Login.jsx manquant\n";
}

// 5. Créer une page de test simple
echo "\n5. Création d'une page de test...\n";
$testPage = 'resources/js/Pages/Test.jsx';
$testContent = 'import { Head } from \'@inertiajs/react\';

export default function Test() {
    return (
        <>
            <Head title="Test" />
            <div className="min-h-screen flex items-center justify-center bg-gray-100">
                <div className="bg-white p-8 rounded-lg shadow-md">
                    <h1 className="text-2xl font-bold text-gray-900 mb-4">
                        Page de Test
                    </h1>
                    <p className="text-gray-600">
                        Si vous voyez cette page, Inertia.js fonctionne correctement.
                    </p>
                </div>
            </div>
        </>
    );
}';

file_put_contents($testPage, $testContent);
echo "✓ Page de test créée: /test\n";

// 6. Ajouter une route de test
echo "\n6. Ajout d\'une route de test...\n";
$webRoutes = file_get_contents('routes/web.php');
if (strpos($webRoutes, 'Route::get(\'/test\'') === false) {
    $testRoute = "\n// Route de test\nRoute::get('/test', function () {\n    return Inertia::render('Test');\n})->name('test');\n";
    file_put_contents('routes/web.php', $webRoutes . $testRoute);
    echo "✓ Route de test ajoutée\n";
} else {
    echo "✓ Route de test existe déjà\n";
}

// 7. Clear caches
echo "\n7. Nettoyage des caches...\n";
system('php artisan config:clear 2>&1');
system('php artisan cache:clear 2>&1');
system('php artisan view:clear 2>&1');
system('php artisan route:clear 2>&1');

echo "✓ Caches nettoyés\n";

// 8. Recompiler les assets
echo "\n8. Recompilation des assets...\n";
echo "Exécution de npm run build...\n";
system('npm run build 2>&1');

echo "\n=== CORRECTION TERMINÉE ===\n";
echo "Maintenant testez:\n";
echo "1. /test - Page de test simple\n";
echo "2. /login - Page de connexion\n";
echo "3. Vérifiez la console du navigateur (F12)\n";
echo "4. Vérifiez les logs: tail -f storage/logs/laravel.log\n"; 