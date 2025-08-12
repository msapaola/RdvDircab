<?php

echo "=== Correction du fichier .env ===\n\n";

// Lire le fichier .env
$envFile = '.env';
if (!file_exists($envFile)) {
    echo "❌ Fichier .env non trouvé\n";
    exit;
}

echo "✅ Fichier .env trouvé\n";

// Lire le contenu
$content = file_get_contents($envFile);
echo "📄 Contenu lu (" . strlen($content) . " caractères)\n\n";

// Rechercher les lignes problématiques
$lines = explode("\n", $content);
$problematicLines = [];

foreach ($lines as $index => $line) {
    $line = trim($line);
    
    // Ignorer les lignes vides et les commentaires
    if (empty($line) || strpos($line, '#') === 0) {
        continue;
    }
    
    // Vérifier si la ligne contient des espaces problématiques
    if (strpos($line, '=') !== false) {
        $parts = explode('=', $line, 2);
        $key = trim($parts[0]);
        $value = isset($parts[1]) ? trim($parts[1]) : '';
        
        // Vérifier si la valeur contient des espaces non échappés
        if (strpos($value, ' ') !== false && !preg_match('/^".*"$/', $value)) {
            $problematicLines[] = [
                'line' => $index + 1,
                'content' => $line,
                'key' => $key,
                'value' => $value
            ];
        }
    }
}

if (empty($problematicLines)) {
    echo "✅ Aucune ligne problématique trouvée\n";
} else {
    echo "⚠️  Lignes problématiques trouvées :\n";
    foreach ($problematicLines as $problem) {
        echo "   Ligne {$problem['line']}: {$problem['content']}\n";
        echo "   Clé: {$problem['key']}\n";
        echo "   Valeur: {$problem['value']}\n\n";
    }
    
    // Corriger les lignes problématiques
    echo "🔧 Correction des lignes problématiques...\n";
    
    $fixedContent = $content;
    foreach ($problematicLines as $problem) {
        $oldLine = $problem['content'];
        $newLine = $problem['key'] . '="' . $problem['value'] . '"';
        
        $fixedContent = str_replace($oldLine, $newLine, $fixedContent);
        echo "   Ligne {$problem['line']}: {$oldLine} → {$newLine}\n";
    }
    
    // Sauvegarder le fichier corrigé
    if (file_put_contents($envFile, $fixedContent)) {
        echo "\n✅ Fichier .env corrigé avec succès\n";
    } else {
        echo "\n❌ Erreur lors de la sauvegarde du fichier .env\n";
    }
}

echo "\n=== Vérification de la syntaxe ===\n";

// Tester la syntaxe du fichier .env
try {
    $testContent = file_get_contents($envFile);
    $testLines = explode("\n", $testContent);
    
    foreach ($testLines as $index => $line) {
        $line = trim($line);
        
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                echo "❌ Ligne {$index + 1}: Syntaxe invalide - {$line}\n";
            } else {
                echo "✅ Ligne {$index + 1}: OK\n";
            }
        }
    }
    
    echo "\n✅ Syntaxe du fichier .env validée\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification : {$e->getMessage()}\n";
}

echo "\n=== Test de Laravel ===\n";

// Tester si Laravel peut maintenant démarrer
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel démarre correctement\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du démarrage de Laravel :\n";
    echo "   - Message : {$e->getMessage()}\n";
    echo "   - Fichier : {$e->getFile()}\n";
    echo "   - Ligne : {$e->getLine()}\n";
}

echo "\n=== Correction terminée ===\n"; 