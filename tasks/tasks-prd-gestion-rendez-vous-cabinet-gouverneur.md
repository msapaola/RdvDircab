# Tasks - Plateforme de Gestion des Rendez-vous
## Cabinet du Gouverneur de Kinshasa

---

## Relevant Files

- `app/Models/Appointment.php` - Modèle principal pour les rendez-vous avec relations et logique métier
- `app/Models/User.php` - Modèle utilisateur étendu avec rôles et permissions
- `app/Models/BlockedSlot.php` - Modèle pour les créneaux bloqués
- `app/Models/ActivityLog.php` - Modèle pour la traçabilité des actions
- `database/migrations/2024_01_01_000001_create_appointments_table.php` - Migration pour la table des rendez-vous
- `database/migrations/2024_01_01_000002_create_blocked_slots_table.php` - Migration pour les créneaux bloqués
- `database/migrations/2024_01_01_000003_create_activity_logs_table.php` - Migration pour les logs d'activité
- `database/seeders/RoleSeeder.php` - Seeder pour créer les rôles (assistant, admin)
- `database/seeders/AdminSeeder.php` - Seeder pour créer le compte administrateur initial
- `app/Http/Controllers/Public/AppointmentController.php` - Contrôleur pour l'interface publique
- `app/Http/Controllers/Admin/DashboardController.php` - Contrôleur pour le tableau de bord admin
- `app/Http/Controllers/Admin/AppointmentController.php` - Contrôleur pour la gestion des RDV
- `app/Http/Controllers/Admin/UserController.php` - Contrôleur pour la gestion des utilisateurs
- `app/Http/Requests/AppointmentRequest.php` - Validation des demandes de rendez-vous
- `app/Http/Requests/AppointmentUpdateRequest.php` - Validation des modifications de RDV
- `app/Http/Middleware/CheckRole.php` - Middleware pour vérifier les rôles utilisateur
- `app/Notifications/AppointmentConfirmation.php` - Notification de confirmation de RDV
- `app/Notifications/AppointmentStatusUpdate.php` - Notification de changement de statut
- `app/Notifications/AppointmentReminder.php` - Notification de rappel
- `app/Notifications/AppointmentExpired.php` - Notification d'expiration
- `resources/js/Pages/Public/Calendar.tsx` - Page publique avec calendrier FullCalendar
- `resources/js/Pages/Public/AppointmentForm.tsx` - Composant modal du formulaire de demande
- `resources/js/Pages/Public/AppointmentTracking.tsx` - Page de suivi des RDV
- `resources/js/Pages/Admin/Dashboard.tsx` - Tableau de bord administrateur
- `resources/js/Pages/Admin/Appointments/Index.tsx` - Liste des rendez-vous avec filtres
- `resources/js/Pages/Admin/Appointments/Show.tsx` - Détail d'un rendez-vous
- `resources/js/Pages/Admin/Users/Index.tsx` - Gestion des utilisateurs (admin uniquement)
- `resources/js/Components/Calendar/FullCalendar.tsx` - Composant FullCalendar réutilisable
- `resources/js/Components/Forms/AppointmentForm.tsx` - Formulaire de demande réutilisable
- `resources/js/Components/Admin/AppointmentTable.tsx` - Tableau des RDV avec actions
- `resources/js/Components/Admin/Statistics.tsx` - Composant pour les graphiques statistiques
- `resources/js/Components/UI/Modal.tsx` - Composant modal réutilisable
- `resources/js/Components/UI/StatusBadge.tsx` - Badge de statut avec couleurs
- `routes/web.php` - Routes principales de l'application
- `routes/admin.php` - Routes pour l'espace d'administration
- `config/appointment.php` - Configuration des paramètres de rendez-vous
- `app/Console/Commands/ExpireAppointments.php` - Commande pour expirer les RDV
- `app/Console/Commands/SendReminders.php` - Commande pour envoyer les rappels
- `app/Jobs/SendAppointmentNotification.php` - Job pour l'envoi d'emails
- `app/Jobs/ProcessAppointmentExpiration.php` - Job pour traiter les expirations
- `app/Http/Controllers/Public AppointmentAccessController.php` - Contrôleur pour le lien sécurisé du demandeur
- `resources/js/Pages/Public/AppointmentAccess.tsx` - Page de consultation et annulation du RDV via lien sécurisé
- `app/Notifications/AppointmentAccessLink.php` - Notification contenant le lien unique sécurisé

- `tests/Feature/AppointmentTest.php` - Tests d'intégration pour les rendez-vous
- `tests/Feature/AdminAppointmentTest.php` - Tests pour l'espace admin
- `tests/Unit/AppointmentModelTest.php` - Tests unitaires pour le modèle Appointment
- `tests/Unit/NotificationTest.php` - Tests pour les notifications

### Notes

- Les tests doivent être placés à côté des fichiers qu'ils testent
- Utiliser `php artisan test` pour exécuter tous les tests
- Utiliser `php artisan test --filter=AppointmentTest` pour exécuter des tests spécifiques
- Les composants React doivent être testés avec Jest et React Testing Library

## Tasks

- [x] 1.0 Configuration initiale et structure de base
  - [x] 1.1 Installer et configurer Inertia.js avec React
  - [x] 1.2 Configurer Laravel Breeze pour l'authentification
  - [x] 1.3 Installer et configurer FullCalendar.js avec adaptateur React
  - [x] 1.4 Installer et configurer Spatie Activity Log
  - [x] 1.5 Installer et configurer les packages pour les graphiques (ApexCharts.js)
  - [x] 1.6 Configurer Tailwind CSS avec les couleurs institutionnelles
  - [x] 1.7 Créer la structure des dossiers pour les composants React
  - [x] 1.8 Configurer les routes principales et admin
  - [x] 1.9 Désactiver l'enregistrement public (/register)

- [x] 2.0 Modèles et base de données
  - [x] 2.1 Créer la migration pour la table appointments avec tous les champs requis
  - [x] 2.2 Créer la migration pour la table blocked_slots
  - [x] 2.3 Créer la migration pour la table activity_logs
  - [x] 2.4 Créer le modèle Appointment avec relations et logique métier
  - [x] 2.5 Créer le modèle BlockedSlot
  - [x] 2.6 Créer le modèle ActivityLog
  - [x] 2.7 Étendre le modèle User avec rôles et permissions
  - [x] 2.8 Créer le seeder RoleSeeder pour les rôles assistant et admin
  - [x] 2.9 Créer le seeder AdminSeeder pour le compte administrateur initial
  - [x] 2.10 Créer le seeder de données de test (optionnel)

- [x] 3.0 Interface publique et calendrier
  - [x] 3.1 Créer le composant FullCalendar réutilisable
  - [x] 3.2 Créer la page publique Calendar.tsx avec le calendrier
  - [x] 3.3 Créer le composant modal AppointmentForm.tsx
  - [x] 3.4 Créer le contrôleur Public/AppointmentController
  - [x] 3.5 Créer les Form Requests pour la validation des demandes
  - [x] 3.6 Implémenter la logique de génération des créneaux disponibles
  - [x] 3.7 Implémenter la logique de blocage automatique (pause déjeuner, créneaux passés)
  - [x] 3.8 Créer la page de suivi AppointmentTracking.tsx
  - [x] 3.9 Implémenter la logique d'annulation par le demandeur
  - [x] 3.10 Créer les composants UI réutilisables (Modal, StatusBadge)
  - [x] 3.11 Générer un secure_token (UUID signé ou hashé) pour chaque demande au moment de la création
  - [x] 3.12 Créer la route publique /suivi/{token} pour accéder au suivi
  - [x] 3.13 Créer la page AppointmentAccess.tsx permettant de :
      - Consulter le statut du rendez-vous
      - Annuler la demande (statut = refusé ou annulé_demandeur)
  - [x] 3.14 Créer le contrôleur AppointmentAccessController pour sécuriser et traiter la demande via token
  - [x] 3.15 Ajouter une ligne dans le tableau appointments :
      - secure_token` (string, unique)
      - canceled_by_requester (boolean, default false)
  - [x] 3.16 Afficher un statut "Annulé par le demandeur" (badge gris) dans l’interface admin
    


- [x] 4.0 Espace d'administration et gestion des RDV
  - [x] 4.1 Créer le middleware CheckRole pour la gestion des permissions
  - [x] 4.2 Créer la page Dashboard.tsx avec KPIs et statistiques
  - [x] 4.3 Créer le contrôleur Admin/DashboardController
  - [x] 4.4 Créer la page Index.tsx pour la liste des rendez-vous
  - [x] 4.5 Créer le composant AppointmentTable.tsx avec filtres et pagination
  - [x] 4.6 Créer le contrôleur Admin/AppointmentController
  - [x] 4.7 Implémenter les actions accepter/refuser/modifier
  - [x] 4.8 Créer la page Show.tsx pour le détail d'un rendez-vous
  - [x] 4.9 Créer le composant StatusBadge pour les statuts
  - [x] 4.10 Créer la page Users/Index.tsx pour la gestion des utilisateurs
  - [x] 4.11 Créer le contrôleur Admin/UserController
  - [x] 4.12 Implémenter la gestion des créneaux bloqués
  - [x] 4.13 Créer le composant Statistics.tsx pour les graphiques

- [x] 5.0 Système de notifications et emails
  - [x] 5.1 Créer la notification AppointmentConfirmation
  - [x] 5.2 Créer la notification AppointmentStatusUpdate
  - [x] 5.3 Créer la notification AppointmentReminder
  - [x] 5.4 Créer la notification AppointmentExpired
  - [x] 5.5 Créer les templates Mailable professionnels
  - [x] 5.6 Implémenter la logique d'envoi automatique des emails
  - [x] 5.7 Créer le job SendAppointmentNotification
  - [x] 5.8 Configurer les templates d'emails avec le lien de suivi
  - [x] 5.9 Créer la notification AppointmentAccessLink envoyée au demandeur avec le lien unique /suivi/{token}
  - [x] 5.10 Mettre à jour le template d’email de confirmation pour inclure :
      - Résumé de la demande
      - Statut initial ("En attente")
      - 🔗 Lien de suivi et d'annulation sécurisé


- [x] 6.0 Sécurité, validation et tâches planifiées
  - [x] 6.1 Implémenter le rate limiting sur le formulaire de demande
  - [x] 6.2 Créer la validation stricte avec Form Requests
  - [x] 6.3 Implémenter la protection CSRF et XSS
  - [x] 6.4 Créer la commande ExpireAppointments pour expirer les RDV
  - [x] 6.5 Créer la commande SendReminders pour les rappels automatiques
  - [x] 6.6 Créer le job ProcessAppointmentExpiration
  - [x] 6.7 Configurer le Laravel Scheduler pour les tâches automatiques
  - [x] 6.8 Implémenter la logique de priorité (officiels, urgences)
  - [x] 6.9 Configurer les logs d'activité avec Spatie Activity Log
  - [x] 6.10 Créer le fichier de configuration appointment.php
  - [x] 6.11 Protéger la route /suivi/{token} avec une vérification de signature et d’expiration optionnelle
  - [x] 6.12 Logguer l’annulation par le demandeur dans ActivityLog avec l’action canceled_by_requester
  - [x] 6.13 Empêcher l’accès au lien sécurisé une fois le rendez-vous expiré ou annulé


- [x] 7.0 Tests et documentation
  - [x] 7.1 Créer les tests d'intégration AppointmentTest.php
  - [x] 7.2 Créer les tests AdminAppointmentTest.php
  - [ ] 7.3 Créer les tests unitaires AppointmentModelTest.php
  - [ ] 7.4 Créer les tests NotificationTest.php
  - [ ] 7.5 Créer le fichier README.md détaillé
  - [ ] 7.6 Documenter l'architecture et les décisions techniques
  - [ ] 7.7 Créer la documentation d'installation complète
  - [ ] 7.8 Documenter les identifiants par défaut
  - [ ] 7.9 Créer un guide utilisateur pour les administrateurs
  - [ ] 7.10 Effectuer les tests finaux et validation complète 
  - [ ] 7.11 Créer les tests AppointmentAccessTest.php pour :
      - Accès valide au lien de suivi
      - Tentative de lien invalide ou expiré
      - Annulation réussie
  - [ ] 7.12 Ajouter des cas de test dans AppointmentModelTest.php pour secure_token et canceled_by_requester
  - [ ] 7.13 Documenter dans le README.md la logique du lien sécurisé pour le demandeur
