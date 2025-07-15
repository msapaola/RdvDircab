<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppointmentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs existants
        $admin = User::where('role', 'admin')->first();
        $assistant = User::where('role', 'assistant')->first();

        if (!$admin || !$assistant) {
            $this->command->error('Veuillez d\'abord exécuter AdminSeeder pour créer les utilisateurs.');
            return;
        }

        // Créer des créneaux bloqués de test
        $this->createBlockedSlots($admin);

        // Créer des rendez-vous de test
        $this->createTestAppointments($admin, $assistant);

        $this->command->info('Données de test créées avec succès!');
    }

    private function createBlockedSlots(User $admin): void
    {
        // Créneaux bloqués pour la pause déjeuner (récurrents)
        BlockedSlot::create([
            'date' => now()->addDays(1),
            'start_time' => '12:00',
            'end_time' => '14:00',
            'type' => BlockedSlot::TYPE_LUNCH,
            'reason' => 'Pause déjeuner',
            'description' => 'Pause déjeuner automatique',
            'blocked_by' => $admin->id,
            'is_recurring' => true,
            'recurrence_type' => BlockedSlot::RECURRENCE_DAILY,
        ]);

        // Créneau bloqué manuel pour demain
        BlockedSlot::create([
            'date' => now()->addDays(1),
            'start_time' => '15:00',
            'end_time' => '17:00',
            'type' => BlockedSlot::TYPE_MEETING,
            'reason' => 'Réunion importante',
            'description' => 'Réunion avec les directeurs',
            'blocked_by' => $admin->id,
            'is_recurring' => false,
        ]);

        $this->command->info('Créneaux bloqués créés.');
    }

    private function createTestAppointments(User $admin, User $assistant): void
    {
        $appointments = [
            // Rendez-vous en attente
            [
                'name' => 'Jean Mukendi',
                'email' => 'jean.mukendi@example.com',
                'phone' => '+243 123 456 789',
                'subject' => 'Demande d\'audience pour projet urbain',
                'message' => 'Je souhaite présenter un projet d\'aménagement urbain pour le quartier de Limete.',
                'preferred_date' => now()->addDays(2),
                'preferred_time' => '09:00',
                'priority' => Appointment::PRIORITY_NORMAL,
                'status' => Appointment::STATUS_PENDING,
                'processed_by' => null,
            ],
            [
                'name' => 'Marie Nzuzi',
                'email' => 'marie.nzuzi@example.com',
                'phone' => '+243 987 654 321',
                'subject' => 'Urgence sanitaire',
                'message' => 'Situation d\'urgence concernant l\'approvisionnement en eau potable.',
                'preferred_date' => now()->addDays(1),
                'preferred_time' => '10:30',
                'priority' => Appointment::PRIORITY_URGENT,
                'status' => Appointment::STATUS_PENDING,
                'processed_by' => null,
            ],
            [
                'name' => 'Ambassade de France',
                'email' => 'ambassade.france@example.com',
                'phone' => '+243 111 222 333',
                'subject' => 'Coopération bilatérale',
                'message' => 'Rencontre pour discuter des projets de coopération entre la France et Kinshasa.',
                'preferred_date' => now()->addDays(3),
                'preferred_time' => '14:00',
                'priority' => Appointment::PRIORITY_OFFICIAL,
                'status' => Appointment::STATUS_PENDING,
                'processed_by' => null,
            ],

            // Rendez-vous acceptés
            [
                'name' => 'Pierre Mwamba',
                'email' => 'pierre.mwamba@example.com',
                'phone' => '+243 444 555 666',
                'subject' => 'Projet de transport public',
                'message' => 'Présentation d\'un projet de modernisation du transport public.',
                'preferred_date' => now()->addDays(4),
                'preferred_time' => '11:00',
                'priority' => Appointment::PRIORITY_NORMAL,
                'status' => Appointment::STATUS_ACCEPTED,
                'processed_by' => $assistant->id,
                'processed_at' => now()->subHours(2),
            ],
            [
                'name' => 'ONU Habitat',
                'email' => 'onuhabitat@example.com',
                'phone' => '+243 777 888 999',
                'subject' => 'Programme de logement social',
                'message' => 'Discussion sur le programme de logement social en partenariat avec l\'ONU.',
                'preferred_date' => now()->addDays(5),
                'preferred_time' => '15:30',
                'priority' => Appointment::PRIORITY_OFFICIAL,
                'status' => Appointment::STATUS_ACCEPTED,
                'processed_by' => $admin->id,
                'processed_at' => now()->subHours(1),
            ],

            // Rendez-vous refusés
            [
                'name' => 'Paul Kabila',
                'email' => 'paul.kabila@example.com',
                'phone' => '+243 123 123 123',
                'subject' => 'Demande de subvention',
                'message' => 'Demande de subvention pour un projet personnel.',
                'preferred_date' => now()->addDays(6),
                'preferred_time' => '16:00',
                'priority' => Appointment::PRIORITY_NORMAL,
                'status' => Appointment::STATUS_REJECTED,
                'processed_by' => $assistant->id,
                'processed_at' => now()->subDays(1),
                'rejection_reason' => 'Le projet ne correspond pas aux critères d\'éligibilité.',
            ],

            // Rendez-vous annulés
            [
                'name' => 'Sophie Tshisekedi',
                'email' => 'sophie.tshisekedi@example.com',
                'phone' => '+243 456 456 456',
                'subject' => 'Événement culturel',
                'message' => 'Organisation d\'un événement culturel dans la ville.',
                'preferred_date' => now()->addDays(7),
                'preferred_time' => '13:00',
                'priority' => Appointment::PRIORITY_NORMAL,
                'status' => Appointment::STATUS_CANCELED,
                'processed_by' => $admin->id,
                'processed_at' => now()->subDays(2),
                'admin_notes' => 'Annulé à la demande du demandeur.',
            ],

            // Rendez-vous expirés
            [
                'name' => 'David Lumumba',
                'email' => 'david.lumumba@example.com',
                'phone' => '+243 789 789 789',
                'subject' => 'Projet éducatif',
                'message' => 'Projet de construction d\'écoles dans les quartiers défavorisés.',
                'preferred_date' => now()->subDays(1),
                'preferred_time' => '10:00',
                'priority' => Appointment::PRIORITY_NORMAL,
                'status' => Appointment::STATUS_EXPIRED,
                'processed_by' => null,
            ],
        ];

        foreach ($appointments as $appointmentData) {
            Appointment::create($appointmentData);
        }

        $this->command->info('Rendez-vous de test créés.');
    }
}
