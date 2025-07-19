<?php

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BlockedSlot;

echo "=== Test des créneaux bloqués ===\n\n";

// Compter tous les créneaux
$total = BlockedSlot::count();
echo "Total créneaux bloqués: {$total}\n";

// Compter ce mois-ci
$thisMonth = BlockedSlot::whereMonth('date', now()->month)->count();
echo "Ce mois-ci: {$thisMonth}\n";

// Compter le mois prochain
$nextMonth = BlockedSlot::whereMonth('date', now()->addMonth()->month)->count();
echo "Mois prochain: {$nextMonth}\n\n";

// Lister tous les créneaux
echo "=== Liste des créneaux ===\n";
$slots = BlockedSlot::orderBy('date', 'asc')->get();

if ($slots->count() > 0) {
    foreach ($slots as $slot) {
        echo "ID: {$slot->id}\n";
        echo "Date: {$slot->date}\n";
        echo "Heures: {$slot->start_time} - {$slot->end_time}\n";
        echo "Raison: {$slot->reason}\n";
        echo "Créé par: {$slot->blocked_by}\n";
        echo "---\n";
    }
} else {
    echo "Aucun créneau trouvé\n";
}

// Test de la pagination
echo "\n=== Test pagination ===\n";
$paginated = BlockedSlot::paginate(15);
echo "Pagination count: " . $paginated->count() . "\n";
echo "Pagination total: " . $paginated->total() . "\n";
echo "Pagination has data: " . ($paginated->count() > 0 ? 'OUI' : 'NON') . "\n";

if ($paginated->count() > 0) {
    echo "Premier élément:\n";
    $first = $paginated->first();
    echo "ID: {$first->id}\n";
    echo "Date: {$first->date}\n";
    echo "Raison: {$first->reason}\n";
} 