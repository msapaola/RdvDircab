<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des rendez-vous
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient toutes les configurations liées aux rendez-vous
    | du Cabinet du Gouverneur de Kinshasa.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Heures d'ouverture
    |--------------------------------------------------------------------------
    */
    'business_hours' => [
        'start' => env('APPOINTMENT_START_TIME', '08:00'),
        'end' => env('APPOINTMENT_END_TIME', '17:00'),
        'lunch_start' => env('APPOINTMENT_LUNCH_START', '12:00'),
        'lunch_end' => env('APPOINTMENT_LUNCH_END', '14:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jours ouvrables
    |--------------------------------------------------------------------------
    | 1 = Lundi, 2 = Mardi, 3 = Mercredi, 4 = Jeudi, 5 = Vendredi
    | 6 = Samedi, 7 = Dimanche
    */
    'working_days' => [1, 2, 3, 4, 5], // Lundi à Vendredi

    /*
    |--------------------------------------------------------------------------
    | Durée des créneaux (en minutes)
    |--------------------------------------------------------------------------
    */
    'slot_duration' => env('APPOINTMENT_SLOT_DURATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Limites de réservation
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_advance_days' => env('APPOINTMENT_MAX_ADVANCE_DAYS', 90), // 3 mois
        'min_advance_hours' => env('APPOINTMENT_MIN_ADVANCE_HOURS', 24), // 24h (sauf urgence)
        'max_requests_per_day' => env('APPOINTMENT_MAX_REQUESTS_PER_DAY', 3),
        'max_requests_per_ip' => env('APPOINTMENT_MAX_REQUESTS_PER_IP', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Expiration des demandes
    |--------------------------------------------------------------------------
    */
    'expiration' => [
        'days' => env('APPOINTMENT_EXPIRATION_DAYS', 30), // 30 jours
        'auto_process' => env('APPOINTMENT_AUTO_EXPIRE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Priorités
    |--------------------------------------------------------------------------
    */
    'priorities' => [
        'normal' => [
            'name' => 'Normale',
            'color' => 'gray',
            'min_advance_hours' => 24,
        ],
        'urgent' => [
            'name' => 'Urgente',
            'color' => 'red',
            'min_advance_hours' => 0, // Pas de délai minimum
        ],
        'official' => [
            'name' => 'Officielle',
            'color' => 'blue',
            'min_advance_hours' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuts
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'pending' => [
            'name' => 'En attente',
            'color' => 'orange',
            'can_cancel_by_requester' => true,
        ],
        'accepted' => [
            'name' => 'Accepté',
            'color' => 'green',
            'can_cancel_by_requester' => true,
        ],
        'rejected' => [
            'name' => 'Refusé',
            'color' => 'red',
            'can_cancel_by_requester' => false,
        ],
        'canceled' => [
            'name' => 'Annulé',
            'color' => 'gray',
            'can_cancel_by_requester' => false,
        ],
        'canceled_by_requester' => [
            'name' => 'Annulé par le demandeur',
            'color' => 'gray',
            'can_cancel_by_requester' => false,
        ],
        'expired' => [
            'name' => 'Expiré',
            'color' => 'gray',
            'can_cancel_by_requester' => false,
        ],
        'completed' => [
            'name' => 'Terminé',
            'color' => 'blue',
            'can_cancel_by_requester' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pièces jointes
    |--------------------------------------------------------------------------
    */
    'attachments' => [
        'max_files' => env('APPOINTMENT_MAX_ATTACHMENTS', 5),
        'max_size' => env('APPOINTMENT_MAX_FILE_SIZE', 5120), // 5MB
        'allowed_types' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'
        ],
        'storage_path' => 'appointments/attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'confirmation' => env('APPOINTMENT_SEND_CONFIRMATION', true),
        'reminders' => [
            'enabled' => env('APPOINTMENT_SEND_REMINDERS', true),
            'days_before' => [1, 3], // Rappels 1 et 3 jours avant
        ],
        'admin_email' => env('APPOINTMENT_ADMIN_EMAIL', 'admin@gouvernorat-kinshasa.cd'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité
    |--------------------------------------------------------------------------
    */
    'security' => [
        'token_length' => env('APPOINTMENT_TOKEN_LENGTH', 32),
        'token_expiry' => env('APPOINTMENT_TOKEN_EXPIRY', 30), // jours
        'rate_limiting' => [
            'enabled' => env('APPOINTMENT_RATE_LIMITING', true),
            'max_attempts' => env('APPOINTMENT_RATE_LIMIT_ATTEMPTS', 5),
            'decay_minutes' => env('APPOINTMENT_RATE_LIMIT_DECAY', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Interface utilisateur
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'calendar_months_ahead' => env('APPOINTMENT_CALENDAR_MONTHS', 2),
        'show_weekends' => env('APPOINTMENT_SHOW_WEEKENDS', false),
        'timezone' => env('APPOINTMENT_TIMEZONE', 'Africa/Kinshasa'),
    ],
]; 