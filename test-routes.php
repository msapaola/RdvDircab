<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Application chargée\n";

try {
    $routes = $app->make('router')->getRoutes();
    echo "Nombre de routes chargées : " . count($routes) . "\n";
    
    foreach ($routes as $route) {
        echo $route->methods()[0] . " " . $route->uri() . " -> " . $route->getName() . "\n";
    }
} catch (Exception $e) {
    echo "Erreur lors du chargement des routes : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
} 