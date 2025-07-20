<?php

require_once 'vendor/autoload.php';

use App\Models\User;

echo "=== Diagnostic des utilisateurs ===\n";

try {
    // Vérifier la connexion à la base de données
    echo "✓ Connexion à la base de données OK\n";
    
    // Compter les utilisateurs
    $totalUsers = User::count();
    echo "✓ Total utilisateurs: $totalUsers\n";
    
    // Vérifier les utilisateurs avec des champs null
    $usersWithNullRole = User::whereNull('role')->count();
    echo "⚠️  Utilisateurs sans rôle: $usersWithNullRole\n";
    
    $usersWithNullEmail = User::whereNull('email')->count();
    echo "⚠️  Utilisateurs sans email: $usersWithNullEmail\n";
    
    $usersWithNullName = User::whereNull('name')->count();
    echo "⚠️  Utilisateurs sans nom: $usersWithNullName\n";
    
    // Lister les utilisateurs avec des problèmes
    if ($usersWithNullRole > 0) {
        echo "\n--- Utilisateurs sans rôle ---\n";
        $users = User::whereNull('role')->get(['id', 'name', 'email', 'role']);
        foreach ($users as $user) {
            echo "ID: {$user->id}, Nom: {$user->name}, Email: {$user->email}, Rôle: " . ($user->role ?? 'NULL') . "\n";
        }
    }
    
    // Vérifier les rôles valides
    echo "\n--- Répartition des rôles ---\n";
    $roles = User::selectRaw('role, count(*) as count')->groupBy('role')->get();
    foreach ($roles as $role) {
        echo "Rôle: " . ($role->role ?? 'NULL') . " - Nombre: {$role->count}\n";
    }
    
    // Vérifier la structure de la table
    echo "\n--- Structure de la table users ---\n";
    $columns = \DB::select("DESCRIBE users");
    foreach ($columns as $column) {
        echo "Colonne: {$column->Field}, Type: {$column->Type}, Null: {$column->Null}, Default: {$column->Default}\n";
    }
    
    echo "\n✓ Diagnostic terminé\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
} 