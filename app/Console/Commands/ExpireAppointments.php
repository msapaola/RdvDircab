<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAppointmentExpiration;
use Illuminate\Console\Command;

class ExpireAppointments extends Command
{
    protected $signature = 'appointments:expire {--dry-run : Afficher les rendez-vous qui seraient expirés sans les traiter}';
    protected $description = 'Expirer automatiquement les rendez-vous non traités depuis plus de 30 jours';

    public function handle()
    {
        $this->info('Traitement de l\'expiration des rendez-vous...');

        if ($this->option('dry-run')) {
            $this->info('Mode test - aucun rendez-vous ne sera expiré');
            $this->showExpiredAppointments();
        } else {
            // Dispatch le job pour traiter les expirations
            ProcessAppointmentExpiration::dispatch();
            $this->info('Job de traitement des expirations dispatché avec succès');
        }

        return Command::SUCCESS;
    }

    protected function showExpiredAppointments()
    {
        $expiredAppointments = \App\Models\Appointment::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(30))
            ->get();

        if ($expiredAppointments->isEmpty()) {
            $this->info('Aucun rendez-vous à expirer');
            return;
        }

        $this->info("{$expiredAppointments->count()} rendez-vous seraient expirés :");
        
        $headers = ['ID', 'Nom', 'Email', 'Objet', 'Date souhaitée', 'Créé le'];
        $rows = [];

        foreach ($expiredAppointments as $appointment) {
            $rows[] = [
                $appointment->id,
                $appointment->name,
                $appointment->email,
                $appointment->subject,
                $appointment->preferred_date,
                $appointment->created_at->format('d/m/Y'),
            ];
        }

        $this->table($headers, $rows);
    }
}
