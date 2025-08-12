<?php

echo "=== Vérification de la configuration SMTP ===\n\n";

// Vérifier si Laravel peut démarrer
echo "🔍 Test de démarrage de Laravel...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel démarre correctement\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du démarrage de Laravel :\n";
    echo "   Message : {$e->getMessage()}\n";
    exit;
}

// Vérifier la configuration mail
echo "\n🔍 Configuration mail actuelle...\n";
try {
    $mailConfig = config('mail');
    echo "✅ Configuration mail chargée\n";
    
    echo "📧 Configuration actuelle :\n";
    echo "   Driver : " . ($mailConfig['default'] ?? 'non défini') . "\n";
    echo "   Host : " . ($mailConfig['mailers']['smtp']['host'] ?? 'non défini') . "\n";
    echo "   Port : " . ($mailConfig['mailers']['smtp']['port'] ?? 'non défini') . "\n";
    echo "   Encryption : " . ($mailConfig['mailers']['smtp']['encryption'] ?? 'non défini') . "\n";
    echo "   Username : " . ($mailConfig['mailers']['smtp']['username'] ?? 'non défini') . "\n";
    echo "   Password : " . (isset($mailConfig['mailers']['smtp']['password']) ? '***' : 'non défini') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la lecture de la configuration mail :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Vérifier les variables d'environnement
echo "\n🔍 Variables d'environnement...\n";
$envVars = [
    'MAIL_MAILER',
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_USERNAME',
    'MAIL_PASSWORD',
    'MAIL_ENCRYPTION',
    'MAIL_FROM_ADDRESS',
    'MAIL_FROM_NAME'
];

foreach ($envVars as $var) {
    $value = env($var);
    if ($value) {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "   {$var} : ***\n";
        } else {
            echo "   {$var} : {$value}\n";
        }
    } else {
        echo "   {$var} : non définie\n";
    }
}

// Test de connexion SMTP
echo "\n🔍 Test de connexion SMTP...\n";
try {
    $host = config('mail.mailers.smtp.host');
    $port = config('mail.mailers.smtp.port');
    
    if ($host && $port) {
        echo "📡 Test de connexion à {$host}:{$port}...\n";
        
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        if ($connection) {
            echo "✅ Connexion réussie à {$host}:{$port}\n";
            fclose($connection);
        } else {
            echo "❌ Impossible de se connecter à {$host}:{$port}\n";
            echo "   Erreur : {$errstr} (Code: {$errno})\n";
        }
    } else {
        echo "⚠️  Host ou port SMTP non configurés\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test de connexion :\n";
    echo "   Message : {$e->getMessage()}\n";
}

// Proposer des alternatives
echo "\n🔍 Alternatives recommandées...\n";
echo "1. **Configuration SMTP alternative :**\n";
echo "   - Gmail (smtp.gmail.com:587)\n";
echo "   - Outlook (smtp-mail.outlook.com:587)\n";
echo "   - SendGrid (smtp.sendgrid.net:587)\n";
echo "   - Mailgun (smtp.mailgun.org:587)\n";

echo "\n2. **Configuration temporaire pour tests :**\n";
echo "   MAIL_MAILER=log\n";
echo "   (Les emails seront enregistrés dans storage/logs/laravel.log)\n";

echo "\n3. **Configuration pour développement :**\n";
echo "   MAIL_MAILER=array\n";
echo "   (Les emails seront stockés en mémoire pour les tests)\n";

// Vérifier si le driver log est disponible
echo "\n🔍 Test du driver log...\n";
try {
    $logMailer = new \Illuminate\Mail\MailManager(app(), config('mail'));
    $logMailer->driver('log');
    echo "✅ Driver log disponible\n";
} catch (Exception $e) {
    echo "❌ Driver log non disponible : {$e->getMessage()}\n";
}

echo "\n=== Vérification terminée ===\n"; 