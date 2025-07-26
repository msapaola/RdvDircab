<?php

echo "=== Configuration SMTP ===\n\n";

// Paramètres SMTP de base pour différents fournisseurs
$smtpConfigs = [
    'gmail' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'description' => 'Gmail SMTP'
    ],
    'outlook' => [
        'host' => 'smtp-mail.outlook.com',
        'port' => 587,
        'encryption' => 'tls',
        'description' => 'Outlook/Hotmail SMTP'
    ],
    'yahoo' => [
        'host' => 'smtp.mail.yahoo.com',
        'port' => 587,
        'encryption' => 'tls',
        'description' => 'Yahoo SMTP'
    ],
    'hostinger' => [
        'host' => 'smtp.hostinger.com',
        'port' => 587,
        'encryption' => 'tls',
        'description' => 'Hostinger SMTP'
    ]
];

echo "Configurations SMTP disponibles :\n";
foreach ($smtpConfigs as $key => $config) {
    echo "- $key: {$config['description']}\n";
    echo "  Host: {$config['host']}\n";
    echo "  Port: {$config['port']}\n";
    echo "  Encryption: {$config['encryption']}\n\n";
}

echo "Pour configurer votre SMTP, ajoutez ces lignes dans votre fichier .env :\n\n";

echo "=== Configuration de base ===\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.votre-fournisseur.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=votre-email@domaine.com\n";
echo "MAIL_PASSWORD=votre-mot-de-passe\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=votre-email@domaine.com\n";
echo "MAIL_FROM_NAME=\"Cabinet du Gouverneur\"\n\n";

echo "=== Exemple pour Gmail ===\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.gmail.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=votre-email@gmail.com\n";
echo "MAIL_PASSWORD=votre-mot-de-passe-app\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=votre-email@gmail.com\n";
echo "MAIL_FROM_NAME=\"Cabinet du Gouverneur\"\n\n";

echo "=== Exemple pour Hostinger ===\n";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.hostinger.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=votre-email@votre-domaine.com\n";
echo "MAIL_PASSWORD=votre-mot-de-passe\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "MAIL_FROM_ADDRESS=votre-email@votre-domaine.com\n";
echo "MAIL_FROM_NAME=\"Cabinet du Gouverneur\"\n\n";

echo "⚠️  Notes importantes :\n";
echo "- Pour Gmail, utilisez un 'mot de passe d'application' (pas votre mot de passe principal)\n";
echo "- Activez l'authentification à 2 facteurs sur Gmail pour générer un mot de passe d'application\n";
echo "- Pour Hostinger, utilisez les paramètres SMTP de votre hébergement\n";
echo "- Testez toujours la configuration avant de passer en production\n\n";

echo "Après avoir configuré le .env, exécutez :\n";
echo "php test-mail-config.php\n"; 