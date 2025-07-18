# Guide d'Administration - Cabinet du Gouverneur

## 🚀 Création de l'utilisateur administrateur

### 1. Exécuter les seeders
```bash
php create-admin.php
```

### 2. Ou exécuter manuellement
```bash
php artisan migrate --force
php artisan db:seed --force
```

## 👤 Identifiants de connexion

### Administrateur Principal
- **Email:** admin@gouvernorat-kinshasa.cd
- **Mot de passe:** Admin@2024!
- **Rôle:** Administrateur complet

### Assistant
- **Email:** assistant@gouvernorat-kinshasa.cd
- **Mot de passe:** Assistant@2024!
- **Rôle:** Assistant (accès limité)

## 🔐 Accès à l'espace admin

### URL d'accès
- **Page de connexion:** `/login`
- **Dashboard admin:** `/admin/dashboard`
- **Gestion des rendez-vous:** `/admin/appointments`
- **Gestion des utilisateurs:** `/admin/users`

### Permissions par rôle

#### Administrateur
- ✅ Accès complet à toutes les fonctionnalités
- ✅ Gestion des utilisateurs
- ✅ Gestion des rendez-vous
- ✅ Gestion des créneaux bloqués
- ✅ Statistiques complètes

#### Assistant
- ✅ Gestion des rendez-vous
- ✅ Gestion des créneaux bloqués
- ✅ Statistiques limitées
- ❌ Gestion des utilisateurs

## 📊 Fonctionnalités disponibles

### Dashboard
- Vue d'ensemble des rendez-vous
- Statistiques en temps réel
- Activités récentes

### Gestion des rendez-vous
- Liste de tous les rendez-vous
- Filtrage par statut, date, priorité
- Acceptation/Rejet de rendez-vous
- Modification des détails
- Annulation de rendez-vous

### Gestion des utilisateurs (Admin uniquement)
- Liste des utilisateurs
- Création de nouveaux utilisateurs
- Modification des rôles
- Activation/Désactivation

### Gestion des créneaux bloqués
- Blocage de créneaux horaires
- Créneaux récurrents
- Gestion des pauses déjeuner
- Gestion des jours fériés

## 🔧 Configuration

### Middleware de rôles
Le système utilise le middleware `CheckRole` pour protéger les routes admin.

### Routes protégées
- `/admin/*` - Requiert authentification + rôle admin/assistant
- `/admin/users/*` - Requiert rôle admin uniquement

## 🛠️ Dépannage

### Problème de connexion
1. Vérifier que les seeders ont été exécutés
2. Vérifier la connexion à la base de données
3. Exécuter `php test-admin-access.php`

### Problème de permissions
1. Vérifier que le middleware `role` est enregistré
2. Vérifier que l'utilisateur a le bon rôle
3. Vérifier que l'utilisateur est actif

### Problème de routes
1. Vérifier que les routes admin sont chargées
2. Exécuter `php artisan route:clear`
3. Vérifier les logs d'erreur

## 📝 Notes importantes

- **Sécurité:** Changez les mots de passe par défaut après la première connexion
- **Sauvegarde:** Faites des sauvegardes régulières de la base de données
- **Logs:** Surveillez les logs d'activité pour détecter les anomalies
- **Mise à jour:** Gardez le système à jour avec les dernières versions

## 🆘 Support

En cas de problème :
1. Vérifiez les logs Laravel (`storage/logs/laravel.log`)
2. Exécutez les scripts de diagnostic
3. Contactez l'équipe technique 