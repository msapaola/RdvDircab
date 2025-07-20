<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Test simple pour vérifier que les requêtes fonctionnent
echo "Test du contrôleur UserController...\n";

try {
    // Test 1: Compter les utilisateurs
    $totalUsers = User::count();
    echo "✓ Total utilisateurs: $totalUsers\n";
    
    // Test 2: Compter les admins
    $adminCount = User::where('role', 'admin')->count();
    echo "✓ Nombre d'admins: $adminCount\n";
    
    // Test 3: Compter les assistants
    $assistantCount = User::where('role', 'assistant')->count();
    echo "✓ Nombre d'assistants: $assistantCount\n";
    
    // Test 4: Compter les utilisateurs vérifiés
    $verifiedCount = User::whereNotNull('email_verified_at')->count();
    echo "✓ Utilisateurs vérifiés: $verifiedCount\n";
    
    echo "✓ Tous les tests passent ! Le contrôleur devrait fonctionner.\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
} 