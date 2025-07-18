<?php

echo "=== CORRECTION PAGES INERTIA ===\n\n";

// 1. Vérifier toutes les pages existantes
echo "1. Vérification des pages existantes...\n";
$pagesDir = 'resources/js/Pages';
$allPages = [];

if (is_dir($pagesDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pagesDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'jsx') {
            $relativePath = str_replace('resources/js/', '', $file->getPathname());
            $allPages[] = $relativePath;
            echo "  ✓ $relativePath\n";
        }
    }
    
    echo "Total: " . count($allPages) . " pages trouvées\n";
} else {
    echo "✗ Dossier Pages manquant\n";
    exit(1);
}

// 2. Créer un fichier d'imports pour forcer l'inclusion
echo "\n2. Création du fichier d'imports...\n";
$importsFile = 'resources/js/pages-imports.js';
$importContent = "// Fichier généré automatiquement pour forcer l'inclusion des pages\n";
$importContent .= "// Ce fichier garantit que toutes les pages sont incluses dans le build Vite\n\n";

foreach ($allPages as $page) {
    $importPath = str_replace('.jsx', '', $page);
    $importContent .= "import '$importPath';\n";
}

file_put_contents($importsFile, $importContent);
echo "✓ Fichier d'imports créé: $importsFile\n";

// 3. Modifier app.jsx pour inclure les imports
echo "\n3. Modification de app.jsx...\n";
$appFile = 'resources/js/app.jsx';
$appContent = file_get_contents($appFile);

// Ajouter l'import des pages au début
$importStatement = "import './pages-imports';\n";
if (strpos($appContent, './pages-imports') === false) {
    $appContent = str_replace(
        "import '../css/app.css';",
        "import '../css/app.css';\n$importStatement",
        $appContent
    );
    file_put_contents($appFile, $appContent);
    echo "✓ Import des pages ajouté à app.jsx\n";
} else {
    echo "✓ Import des pages déjà présent\n";
}

// 4. Nettoyer les anciens assets
echo "\n4. Nettoyage des anciens assets...\n";
$buildDir = 'public/build';
if (is_dir($buildDir)) {
    $files = glob($buildDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ Anciens assets supprimés\n";
}

// 5. Reconstruire avec Vite
echo "\n5. Reconstruction avec Vite...\n";
echo "Exécution de npm run build...\n";
$output = shell_exec('npm run build 2>&1');
echo $output;

// 6. Vérifier le nouveau manifest
echo "\n6. Vérification du nouveau manifest...\n";
$manifestFile = 'public/build/manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ Nouveau manifest créé\n";
        
        // Vérifier les pages importantes
        $importantPages = [
            'resources/js/Pages/Test.jsx',
            'resources/js/Pages/Auth/Login.jsx',
            'resources/js/Pages/Auth/LoginSimple.jsx',
            'resources/js/pages-imports.js'
        ];
        
        foreach ($importantPages as $page) {
            if (isset($manifest[$page])) {
                echo "✓ $page dans le manifest\n";
            } else {
                echo "✗ $page manquant dans le manifest\n";
            }
        }
        
        echo "\nToutes les entrées du manifest:\n";
        foreach ($manifest as $key => $value) {
            echo "  - $key\n";
        }
    } else {
        echo "✗ Nouveau manifest invalide\n";
    }
} else {
    echo "✗ Nouveau manifest non créé\n";
}

// 7. Nettoyer les caches Laravel
echo "\n7. Nettoyage des caches Laravel...\n";
system('php artisan config:clear 2>&1');
system('php artisan cache:clear 2>&1');
system('php artisan view:clear 2>&1');
system('php artisan route:clear 2>&1');
echo "✓ Caches nettoyés\n";

// 8. Test final
echo "\n8. Test final...\n";
try {
    $app = require_once 'bootstrap/app.php';
    
    $request = new \Illuminate\Http\Request();
    $request->setMethod('GET');
    $request->setUri('/test');
    
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Page de test accessible\n";
    } else {
        echo "⚠ Page de test retourne status: " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test: " . $e->getMessage() . "\n";
}

echo "\n=== CORRECTION TERMINÉE ===\n";
echo "Maintenant testez:\n";
echo "1. /test - Page de test\n";
echo "2. /login-simple - Login simplifié\n";
echo "3. /login - Login original\n";
echo "\nSi le problème persiste:\n";
echo "1. Vérifiez la console du navigateur (F12)\n";
echo "2. Vérifiez que le manifest contient toutes les pages\n";
echo "3. Vérifiez les logs d'erreur\n"; 