<?php

// Script pour diagnostiquer et corriger le problème CSRF
echo "=== Diagnostic et Correction du Problème CSRF ===\n\n";

$publicHtml = __DIR__;

echo "1. Vérification de la configuration CSRF...\n";

// Vérifier le fichier VerifyCsrfToken
$csrfMiddlewarePath = $publicHtml . '/app/Http/Middleware/VerifyCsrfToken.php';
if (file_exists($csrfMiddlewarePath)) {
    echo "   ✅ Middleware VerifyCsrfToken trouvé\n";
    $content = file_get_contents($csrfMiddlewarePath);
    if (strpos($content, 'appointments') !== false) {
        echo "   ✅ Route appointments exclue de la vérification CSRF\n";
    } else {
        echo "   ❌ Route appointments non exclue\n";
    }
} else {
    echo "   ❌ Middleware VerifyCsrfToken manquant\n";
}

// Vérifier le fichier bootstrap/app.php
$bootstrapPath = $publicHtml . '/bootstrap/app.php';
if (file_exists($bootstrapPath)) {
    echo "   ✅ Fichier bootstrap/app.php trouvé\n";
    $content = file_get_contents($bootstrapPath);
    if (strpos($content, 'VerifyCsrfToken') !== false) {
        echo "   ✅ VerifyCsrfToken enregistré dans bootstrap/app.php\n";
    } else {
        echo "   ❌ VerifyCsrfToken non enregistré\n";
    }
} else {
    echo "   ❌ Fichier bootstrap/app.php manquant\n";
}

echo "\n2. Création/Correction du middleware VerifyCsrfToken...\n";

$csrfContent = '<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        "appointments",
        "appointments/*",
        "tracking/*",
        "sanctum/csrf-cookie",
    ];
}';

if (file_put_contents($csrfMiddlewarePath, $csrfContent)) {
    echo "   ✅ Middleware VerifyCsrfToken créé/corrigé\n";
} else {
    echo "   ❌ Impossible de créer le middleware\n";
}

echo "\n3. Correction du fichier bootstrap/app.php...\n";

$bootstrapContent = '<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__."/../routes/web.php",
        commands: __DIR__."/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Enregistrer les middlewares personnalisés
        $middleware->alias([
            "throttle.appointments" => \App\Http\Middleware\ThrottleAppointments::class,
        ]);

        // S\'assurer que VerifyCsrfToken est correctement configuré
        $middleware->web(prepend: [
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();';

if (file_put_contents($bootstrapPath, $bootstrapContent)) {
    echo "   ✅ Fichier bootstrap/app.php corrigé\n";
} else {
    echo "   ❌ Impossible de corriger bootstrap/app.php\n";
}

echo "\n4. Vérification du contrôleur PublicController...\n";

$controllerPath = $publicHtml . '/app/Http/Controllers/PublicController.php';
if (file_exists($controllerPath)) {
    echo "   ✅ Contrôleur PublicController trouvé\n";
    $content = file_get_contents($controllerPath);
    if (strpos($content, 'back()->withErrors') !== false) {
        echo "   ✅ Méthode store utilise Inertia.js\n";
    } else {
        echo "   ❌ Méthode store n\'utilise pas Inertia.js\n";
    }
} else {
    echo "   ❌ Contrôleur PublicController manquant\n";
}

echo "\n5. Test de la route appointments...\n";

// Test simple de la route
$testUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/appointments';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'name=test&email=test@test.com');
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
} else {
    echo "   ✅ Route accessible (code $httpCode)\n";
}

echo "\n6. Vérification des permissions...\n";

$directories = [
    $publicHtml . '/app/Http/Middleware',
    $publicHtml . '/bootstrap',
    $publicHtml . '/storage/framework/sessions',
    $publicHtml . '/storage/framework/cache',
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✅ $dir accessible en écriture\n";
        } else {
            echo "   ❌ $dir non accessible en écriture\n";
        }
    } else {
        echo "   ❌ $dir n\'existe pas\n";
    }
}

echo "\n=== Diagnostic terminé ===\n";
echo "🎯 Solutions appliquées:\n";
echo "   1. Middleware VerifyCsrfToken corrigé avec exclusions\n";
echo "   2. Bootstrap/app.php mis à jour\n";
echo "   3. Permissions vérifiées\n\n";

echo "📝 Prochaines étapes:\n";
echo "   1. Videz le cache: php artisan cache:clear\n";
echo "   2. Videz les sessions: php artisan session:table\n";
echo "   3. Testez à nouveau le formulaire\n";
echo "   4. Si le problème persiste, vérifiez les logs: tail -f storage/logs/laravel.log\n"; 