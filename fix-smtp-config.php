<?php

echo "=== Vérification et Correction de la Configuration SMTP ===\n\n";

// Vérifier si le fichier .env existe
if (!file_exists('.env')) {
    echo "❌ Fichier .env non trouvé. Création d'un fichier .env de base...\n";
    
    $envContent = "APP_NAME=\"Cabinet du Gouverneur\"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://green-wolverine-495039.hostingersite.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=gouv.kinshasa.cd
MAIL_PORT=465
MAIL_USERNAME=contact@gouv.kinshasa.cd
MAIL_PASSWORD=your_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=contact@gouv.kinshasa.cd
MAIL_FROM_NAME=\"Cabinet du Gouverneur\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME=\"Cabinet du Gouverneur\"
VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"
VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"
VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"
VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"
";
    
    file_put_contents('.env', $envContent);
    echo "✅ Fichier .env créé avec la configuration SMTP de base\n\n";
} else {
    echo "✅ Fichier .env trouvé\n";
}

// Lire le contenu actuel du .env
$envContent = file_get_contents('.env');
$lines = explode("\n", $envContent);

// Configuration SMTP recommandée
$smtpConfig = [
    'MAIL_MAILER' => 'smtp',
    'MAIL_HOST' => 'gouv.kinshasa.cd',
    'MAIL_PORT' => '465',
    'MAIL_USERNAME' => 'contact@gouv.kinshasa.cd',
    'MAIL_ENCRYPTION' => 'ssl',
    'MAIL_FROM_ADDRESS' => 'contact@gouv.kinshasa.cd',
    'MAIL_FROM_NAME' => '"Cabinet du Gouverneur"'
];

echo "Configuration SMTP recommandée :\n";
foreach ($smtpConfig as $key => $value) {
    echo "$key=$value\n";
}
echo "\n";

// Vérifier et corriger chaque paramètre
$updated = false;
foreach ($smtpConfig as $key => $value) {
    $found = false;
    foreach ($lines as $i => $line) {
        if (strpos($line, $key . '=') === 0) {
            $currentValue = substr($line, strlen($key) + 1);
            if ($currentValue !== $value) {
                echo "🔄 Mise à jour $key: '$currentValue' → '$value'\n";
                $lines[$i] = "$key=$value";
                $updated = true;
            } else {
                echo "✅ $key: OK ($value)\n";
            }
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "➕ Ajout: $key=$value\n";
        $lines[] = "$key=$value";
        $updated = true;
    }
}

if ($updated) {
    $newContent = implode("\n", $lines);
    file_put_contents('.env', $newContent);
    echo "\n✅ Configuration .env mise à jour\n";
} else {
    echo "\n✅ Configuration SMTP déjà correcte\n";
}

echo "\n⚠️  IMPORTANT: Assurez-vous de définir le bon mot de passe dans MAIL_PASSWORD\n";
echo "Exécutez ensuite: php test-mail-config.php\n"; 