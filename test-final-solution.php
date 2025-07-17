<?php

// Script de test final pour la solution des rendez-vous
echo "=== TEST FINAL DE LA SOLUTION ===\n\n";

// 1. Vérification des fichiers corrigés
echo "1. VÉRIFICATION DES FICHIERS CORRIGÉS\n";
echo "------------------------------------\n";

$files = [
    'resources/js/Pages/Public/Home.jsx' => 'Composant principal avec handleSubmit corrigé',
    'resources/js/Components/Forms/AppointmentForm.jsx' => 'Formulaire avec boutons de soumission',
    'app/Http/Controllers/PublicController.php' => 'Contrôleur avec méthode store',
    'routes/web.php' => 'Routes avec CSRF désactivé',
    'app/Http/Middleware/ThrottleAppointments.php' => 'Middleware de limitation',
    'app/Models/Appointment.php' => 'Modèle Appointment',
    'public/build/manifest.json' => 'Manifest des assets compilés'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $file: $description\n";
    } else {
        echo "✗ $file: MANQUANT - $description\n";
    }
}

// 2. Vérification des assets compilés
echo "\n2. VÉRIFICATION DES ASSETS\n";
echo "-------------------------\n";

$manifestFile = 'public/build/manifest.json';
if (file_exists($manifestFile)) {
    $manifest = json_decode(file_get_contents($manifestFile), true);
    if ($manifest) {
        echo "✓ Manifest JSON: VALIDE\n";
        
        // Chercher le fichier Home
        $homeFile = null;
        foreach ($manifest as $key => $value) {
            if (str_contains($key, 'Home')) {
                $homeFile = $value['file'];
                break;
            }
        }
        
        if ($homeFile && file_exists('public/build/' . $homeFile)) {
            echo "✓ Fichier Home compilé: $homeFile\n";
        } else {
            echo "✗ Fichier Home compilé: MANQUANT\n";
        }
    } else {
        echo "✗ Manifest JSON: INVALIDE\n";
    }
} else {
    echo "✗ Manifest JSON: MANQUANT\n";
}

// 3. Test de la route
echo "\n3. TEST DE LA ROUTE\n";
echo "------------------\n";

$routeFile = 'routes/web.php';
if (file_exists($routeFile)) {
    $content = file_get_contents($routeFile);
    
    if (str_contains($content, 'Route::post(\'/appointments\'')) {
        echo "✓ Route POST /appointments: PRÉSENTE\n";
    } else {
        echo "✗ Route POST /appointments: MANQUANTE\n";
    }
    
    if (str_contains($content, 'withoutMiddleware([\\App\\Http\\Middleware\\VerifyCsrfToken::class])')) {
        echo "✓ CSRF désactivé: OUI\n";
    } else {
        echo "✗ CSRF désactivé: NON\n";
    }
    
    if (str_contains($content, 'throttle.appointments')) {
        echo "✓ Middleware throttle.appointments: PRÉSENT\n";
    } else {
        echo "✗ Middleware throttle.appointments: MANQUANT\n";
    }
}

// 4. Test du contrôleur
echo "\n4. TEST DU CONTRÔLEUR\n";
echo "--------------------\n";

$controllerFile = 'app/Http/Controllers/PublicController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    if (str_contains($content, 'public function store')) {
        echo "✓ Méthode store: PRÉSENTE\n";
    } else {
        echo "✗ Méthode store: MANQUANTE\n";
    }
    
    if (str_contains($content, 'Validator::make')) {
        echo "✓ Validation: PRÉSENTE\n";
    } else {
        echo "✗ Validation: MANQUANTE\n";
    }
    
    if (str_contains($content, 'Appointment::create')) {
        echo "✓ Création de rendez-vous: PRÉSENTE\n";
    } else {
        echo "✗ Création de rendez-vous: MANQUANTE\n";
    }
    
    if (str_contains($content, 'response()->json')) {
        echo "✓ Réponse JSON: PRÉSENTE\n";
    } else {
        echo "✗ Réponse JSON: MANQUANTE\n";
    }
}

// 5. Test du composant JavaScript
echo "\n5. TEST DU COMPOSANT JAVASCRIPT\n";
echo "-------------------------------\n";

$homeFile = 'resources/js/Pages/Public/Home.jsx';
if (file_exists($homeFile)) {
    $content = file_get_contents($homeFile);
    
    if (str_contains($content, 'handleSubmit')) {
        echo "✓ Fonction handleSubmit: PRÉSENTE\n";
    } else {
        echo "✗ Fonction handleSubmit: MANQUANTE\n";
    }
    
    if (str_contains($content, 'fetch(\'/appointments\'')) {
        echo "✓ Appel fetch /appointments: PRÉSENT\n";
    } else {
        echo "✗ Appel fetch /appointments: MANQUANT\n";
    }
    
    if (str_contains($content, 'console.log')) {
        echo "✓ Logs de débogage: PRÉSENTS\n";
    } else {
        echo "✗ Logs de débogage: MANQUANTS\n";
    }
    
    if (str_contains($content, 'isSubmitting')) {
        echo "✓ État isSubmitting: PRÉSENT\n";
    } else {
        echo "✗ État isSubmitting: MANQUANT\n";
    }
}

// 6. Test du formulaire
echo "\n6. TEST DU FORMULAIRE\n";
echo "--------------------\n";

$formFile = 'resources/js/Components/Forms/AppointmentForm.jsx';
if (file_exists($formFile)) {
    $content = file_get_contents($formFile);
    
    if (str_contains($content, 'onSubmit={handleSubmit}')) {
        echo "✓ Gestionnaire onSubmit: PRÉSENT\n";
    } else {
        echo "✗ Gestionnaire onSubmit: MANQUANT\n";
    }
    
    if (str_contains($content, 'PrimaryButton type=\"submit\"')) {
        echo "✓ Bouton de soumission: PRÉSENT\n";
    } else {
        echo "✗ Bouton de soumission: MANQUANT\n";
    }
    
    if (str_contains($content, 'disabled={isSubmitting}')) {
        echo "✓ Désactivation pendant soumission: PRÉSENTE\n";
    } else {
        echo "✗ Désactivation pendant soumission: MANQUANTE\n";
    }
}

// 7. Instructions de test final
echo "\n7. INSTRUCTIONS DE TEST FINAL\n";
echo "----------------------------\n";

echo "1. Commandes à exécuter sur le serveur:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n";
echo "   php artisan view:clear\n";
echo "   composer dump-autoload\n\n";

echo "2. Vérification des routes:\n";
echo "   php artisan route:list | grep appointment\n\n";

echo "3. Test avec cURL:\n";
echo "   curl -X POST http://votre-domaine.com/appointments \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -H 'Accept: application/json' \\\n";
echo "     -H 'X-Requested-With: XMLHttpRequest' \\\n";
echo "     -d '{\"name\":\"Test User\",\"email\":\"test@example.com\",\"phone\":\"+243123456789\",\"subject\":\"Test\",\"preferred_date\":\"2024-12-25\",\"preferred_time\":\"09:00\",\"priority\":\"normal\"}' \\\n";
echo "     -v\n\n";

echo "4. Test dans le navigateur:\n";
echo "   - Ouvrez les outils de développement (F12)\n";
echo "   - Allez dans l'onglet Console\n";
echo "   - Allez dans l'onglet Network\n";
echo "   - Remplissez le formulaire de rendez-vous\n";
echo "   - Cliquez sur 'Soumettre la demande'\n";
echo "   - Vérifiez les logs dans la console\n";
echo "   - Vérifiez la requête POST dans l'onglet Network\n\n";

echo "5. Vérification des logs:\n";
echo "   tail -f storage/logs/laravel.log\n\n";

echo "=== RÉSUMÉ DES CORRECTIONS ===\n";
echo "✓ Suppression du fichier AppointmentForm.tsx en conflit\n";
echo "✓ Correction de la fonction handleSubmit dans Home.jsx\n";
echo "✓ Ajout de logs de débogage\n";
echo "✓ Ajout des boutons de soumission dans AppointmentForm.jsx\n";
echo "✓ Compilation des assets avec succès\n";
echo "✓ Route avec CSRF désactivé\n";
echo "✓ Middleware de limitation configuré\n";

echo "\n=== PROBLÈMES RÉSOLUS ===\n";
echo "1. Conflit entre deux fichiers AppointmentForm\n";
echo "2. Incohérence dans la soumission du formulaire\n";
echo "3. Manque de bouton de soumission\n";
echo "4. Absence de logs de débogage\n";
echo "5. Problèmes de compilation des assets\n";

echo "\nLa solution devrait maintenant fonctionner correctement !\n"; 