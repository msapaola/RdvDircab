<?php

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BlockedSlot;
use Illuminate\Http\Request;

echo "=== Test direct de la requête blockedSlots ===\n\n";

// Simuler exactement ce que fait le contrôleur
$query = BlockedSlot::query();

// Filtres (aucun pour ce test)
// Tri
$sortBy = 'date';
$sortOrder = 'asc';
$query->orderBy($sortBy, $sortOrder);

// Pagination
$blockedSlots = $query->paginate(15)->withQueryString();

// Statistiques
$stats = [
    'total' => BlockedSlot::count(),
    'this_month' => BlockedSlot::whereMonth('date', now()->month)->count(),
    'next_month' => BlockedSlot::whereMonth('date', now()->addMonth()->month)->count(),
];

echo "=== Statistiques ===\n";
echo "Total: " . $stats['total'] . "\n";
echo "Ce mois-ci: " . $stats['this_month'] . "\n";
echo "Mois prochain: " . $stats['next_month'] . "\n\n";

echo "=== Pagination ===\n";
echo "Total paginé: " . $blockedSlots->total() . "\n";
echo "Count paginé: " . $blockedSlots->count() . "\n";
echo "Has data: " . ($blockedSlots->count() > 0 ? 'OUI' : 'NON') . "\n";
echo "From: " . $blockedSlots->firstItem() . "\n";
echo "To: " . $blockedSlots->lastItem() . "\n\n";

if ($blockedSlots->count() > 0) {
    echo "=== Premier élément (avant load) ===\n";
    $first = $blockedSlots->first();
    echo "ID: " . $first->id . "\n";
    echo "Date: " . $first->date . "\n";
    echo "Reason: " . $first->reason . "\n";
    echo "Blocked by ID: " . $first->blocked_by . "\n";
    echo "Blocked by relation: " . ($first->blockedBy ? $first->blockedBy->name : 'NULL') . "\n\n";
    
    echo "=== Test du load blockedBy ===\n";
    $blockedSlotsWithRelations = $blockedSlots->load('blockedBy');
    $firstWithRelation = $blockedSlotsWithRelations->first();
    echo "Après load - Blocked by: " . ($firstWithRelation->blockedBy ? $firstWithRelation->blockedBy->name : 'NULL') . "\n\n";
    
    echo "=== Conversion en array ===\n";
    $blockedSlotsArray = $blockedSlotsWithRelations->toArray();
    echo "Array keys: " . implode(', ', array_keys($blockedSlotsArray)) . "\n";
    echo "Data count: " . count($blockedSlotsArray['data']) . "\n";
    
    if (count($blockedSlotsArray['data']) > 0) {
        echo "Premier élément en array:\n";
        $firstArray = $blockedSlotsArray['data'][0];
        echo "ID: " . $firstArray['id'] . "\n";
        echo "Date: " . $firstArray['date'] . "\n";
        echo "Reason: " . $firstArray['reason'] . "\n";
        echo "Blocked by: " . json_encode($firstArray['blocked_by']) . "\n";
    }
} else {
    echo "❌ Aucun créneau trouvé dans la pagination\n";
} 