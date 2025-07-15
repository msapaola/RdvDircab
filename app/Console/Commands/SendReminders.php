<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Jobs\SendAppointmentNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendReminders extends Command
{
    protected $signature = 'appointments:send-reminders {--days=1 : Nombre de jours avant le rendez-vous} {--dry-run : Afficher les rappels qui seraient envoyés sans les envoyer}';
    protected $description = 'Envoyer les rappels pour les rendez-vous acceptés';

    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Envoi des rappels pour les rendez-vous dans {$days} jour(s)...");

        if ($this->option('dry-run')) {
            $this->info('Mode test - aucun rappel ne sera envoyé');
            $this->showReminders($days);
        } else {
            $this->sendReminders($days);
        }

        return Command::SUCCESS;
    }

    protected function sendReminders($days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');
        
        $appointments = Appointment::where('status', 'accepted')
            ->where('preferred_date', $targetDate)
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            try {
                SendAppointmentNotification::dispatch($appointment, 'reminder', [
                    'days_until' => $days,
                ]);
                $count++;
                
                $this->line("Rappel envoyé pour le rendez-vous #{$appointment->id} - {$appointment->name}");
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'envoi du rappel pour le rendez-vous #{$appointment->id}: {$e->getMessage()}");
            }
        }

        $this->info("{$count} rappel(s) envoyé(s) avec succès");
    }

    protected function showReminders($days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');
        
        $appointments = Appointment::where('status', 'accepted')
            ->where('preferred_date', $targetDate)
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('Aucun rappel à envoyer');
            return;
        }

        $this->info("{$appointments->count()} rappel(s) seraient envoyés pour le {$targetDate} :");
        
        $headers = ['ID', 'Nom', 'Email', 'Objet', 'Heure', 'Créé le'];
        $rows = [];

        foreach ($appointments as $appointment) {
            $rows[] = [
                $appointment->id,
                $appointment->name,
                $appointment->email,
                $appointment->subject,
                $appointment->preferred_time,
                $appointment->created_at->format('d/m/Y'),
            ];
        }

        $this->table($headers, $rows);
    }
}
