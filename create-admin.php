<?php

echo "=== CRÉATION UTILISATEUR ADMIN ===\n\n";

// 1. Vérifier si les migrations sont à jour
echo "1. Vérification des migrations...\n";
$output = shell_exec('php artisan migrate:status 2>&1');
echo $output . "\n";

// 2. Exécuter les migrations si nécessaire
echo "2. Exécution des migrations...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output . "\n";

// 3. Exécuter les seeders
echo "3. Exécution des seeders...\n";
$output = shell_exec('php artisan db:seed --force 2>&1');
echo $output . "\n";

// 4. Vérifier les utilisateurs créés
echo "4. Vérification des utilisateurs...\n";
try {
    $app = require_once 'bootstrap/app.php';
    $users = \App\Models\User::all();
    
    if ($users->count() > 0) {
        echo "Utilisateurs créés:\n";
        foreach ($users as $user) {
            echo "- {$user->name} ({$user->email}) - Rôle: {$user->role}\n";
        }
    } else {
        echo "Aucun utilisateur trouvé.\n";
    }
} catch (Exception $e) {
    echo "Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n=== CRÉATION TERMINÉE ===\n";
echo "Vous pouvez maintenant vous connecter avec:\n";
echo "Email: admin@gouvernorat-kinshasa.cd\n";
echo "Mot de passe: Admin@2024!\n";
echo "\nOu avec l'assistant:\n";
echo "Email: assistant@gouvernorat-kinshasa.cd\n";
echo "Mot de passe: Assistant@2024!\n"; 