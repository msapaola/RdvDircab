<?php

// Script pour créer un .htaccess qui permet l'accès aux assets
echo "=== Création du fichier .htaccess ===\n\n";

$publicHtml = __DIR__;
$htaccessPath = $publicHtml . '/.htaccess';

echo "1. Vérification du .htaccess existant...\n";
if (file_exists($htaccessPath)) {
    echo "   ✅ Fichier .htaccess existant trouvé\n";
    $backupPath = $htaccessPath . '.backup.' . date('Y-m-d-H-i-s');
    copy($htaccessPath, $backupPath);
    echo "   💾 Sauvegarde créée: " . basename($backupPath) . "\n";
} else {
    echo "   ℹ️  Aucun fichier .htaccess existant\n";
}

echo "\n2. Création du nouveau .htaccess...\n";

$htaccessContent = <<<'HTACCESS'
# Configuration pour Laravel + Assets
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Permettre l'accès direct aux assets
    RewriteCond %{REQUEST_URI} ^/build/assets/.*\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$
    RewriteRule ^(.*)$ public/$1 [L]
    
    # Permettre l'accès au manifest
    RewriteCond %{REQUEST_URI} ^/build/manifest\.json$
    RewriteRule ^(.*)$ public/$1 [L]
    
    # Permettre l'accès aux autres fichiers publics
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ public/index.php [L]
</IfModule>

# Headers de sécurité pour les assets
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    Header set Cache-Control "public, max-age=31536000"
    Header set X-Content-Type-Options "nosniff"
</FilesMatch>

# Protection des fichiers sensibles
<FilesMatch "\.(env|log|sql|md|txt|lock|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Permettre l'accès au manifest.json
<Files "manifest.json">
    Order allow,deny
    Allow from all
</Files>

# Configuration PHP
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
</IfModule>

# Compression GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
HTACCESS;

if (file_put_contents($htaccessPath, $htaccessContent)) {
    echo "   ✅ Fichier .htaccess créé avec succès\n";
} else {
    echo "   ❌ Impossible de créer le fichier .htaccess\n";
    exit(1);
}

echo "\n3. Vérification des permissions...\n";
if (chmod($htaccessPath, 0644)) {
    echo "   ✅ Permissions 644 pour .htaccess\n";
} else {
    echo "   ❌ Impossible de changer les permissions\n";
}

echo "\n4. Test de la configuration...\n";

// Vérifier si mod_rewrite est disponible
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "   ✅ Mod_rewrite disponible\n";
    } else {
        echo "   ⚠️  Mod_rewrite non disponible - contactez votre hébergeur\n";
    }
} else {
    echo "   ℹ️  Impossible de vérifier mod_rewrite\n";
}

echo "\n=== Configuration terminée ===\n";
echo "🎯 Le fichier .htaccess a été configuré pour:\n";
echo "   ✅ Permettre l'accès aux assets via /build/assets/\n";
echo "   ✅ Permettre l'accès au manifest.json\n";
echo "   ✅ Rediriger les autres requêtes vers Laravel\n";
echo "   ✅ Protéger les fichiers sensibles\n";
echo "   ✅ Optimiser les performances (cache, compression)\n\n";

echo "📝 Prochaines étapes:\n";
echo "   1. Testez l'accès à: https://votre-domaine.com/build/assets/app-8cgd_IZT.css\n";
echo "   2. Si ça ne fonctionne pas, essayez: https://votre-domaine.com/public/build/assets/app-8cgd_IZT.css\n";
echo "   3. Vérifiez les logs d'erreur du serveur\n"; 