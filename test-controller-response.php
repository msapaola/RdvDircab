<?php

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\AppointmentController;
use Illuminate\Http\Request;

echo "=== Test de la réponse du contrôleur blockedSlots ===\n\n";

// Créer une requête simulée
$request = new Request();

// Créer une instance du contrôleur
$controller = new AppointmentController();

// Appeler la méthode blockedSlots
try {
    $response = $controller->blockedSlots($request);
    
    echo "✅ Réponse obtenue avec succès\n";
    echo "Type de réponse: " . get_class($response) . "\n\n";
    
    // Si c'est une réponse Inertia, extraire les données
    if (method_exists($response, 'getData')) {
        $data = $response->getData();
        echo "=== Données de la réponse ===\n";
        echo "Données complètes: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        
        if (isset($data['props']['blockedSlots'])) {
            $blockedSlots = $data['props']['blockedSlots'];
            echo "=== BlockedSlots ===\n";
            echo "Total: " . ($blockedSlots['total'] ?? 'N/A') . "\n";
            echo "Count: " . ($blockedSlots['count'] ?? 'N/A') . "\n";
            echo "Has data: " . (isset($blockedSlots['data']) && count($blockedSlots['data']) > 0 ? 'OUI' : 'NON') . "\n";
            echo "Data length: " . (isset($blockedSlots['data']) ? count($blockedSlots['data']) : 0) . "\n";
            
            if (isset($blockedSlots['data']) && count($blockedSlots['data']) > 0) {
                echo "\n=== Premier élément ===\n";
                $first = $blockedSlots['data'][0];
                echo "ID: " . ($first['id'] ?? 'N/A') . "\n";
                echo "Date: " . ($first['date'] ?? 'N/A') . "\n";
                echo "Reason: " . ($first['reason'] ?? 'N/A') . "\n";
                echo "Blocked by: " . (isset($first['blocked_by']) ? json_encode($first['blocked_by']) : 'N/A') . "\n";
            }
        }
        
        if (isset($data['props']['stats'])) {
            echo "\n=== Stats ===\n";
            $stats = $data['props']['stats'];
            echo "Total: " . ($stats['total'] ?? 'N/A') . "\n";
            echo "This month: " . ($stats['this_month'] ?? 'N/A') . "\n";
            echo "Next month: " . ($stats['next_month'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ La réponse n'a pas de méthode getData()\n";
        echo "Contenu de la réponse: " . $response . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'appel du contrôleur:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
} 