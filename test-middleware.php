<?php

require_once 'vendor/autoload.php';

use App\Http\Middleware\CheckActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

echo "=== Test du middleware CheckActive ===\n";

try {
    // Test 1: Vérifier si la classe peut être instanciée
    echo "1. Test d'instanciation de la classe...\n";
    $middleware = new CheckActive();
    echo "✓ Classe instanciée avec succès\n";
    
    // Test 2: Vérifier si la méthode handle existe
    echo "2. Test de la méthode handle...\n";
    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('handle');
    echo "✓ Méthode handle trouvée\n";
    
    // Test 3: Vérifier les paramètres
    echo "3. Test des paramètres...\n";
    $params = $method->getParameters();
    echo "✓ Paramètres: " . count($params) . " trouvés\n";
    
    echo "\n=== Tests terminés avec succès ===\n";
    echo "Le middleware CheckActive semble correctement défini.\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
} 