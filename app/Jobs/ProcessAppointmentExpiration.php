<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Jobs\SendAppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessAppointmentExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    public function handle()
    {
        $expirationDate = Carbon::now()->subDays(30);
        
        // Récupérer tous les rendez-vous en attente depuis plus de 30 jours
        $expiredAppointments = Appointment::where('status', 'pending')
            ->where('created_at', '<=', $expirationDate)
            ->get();

        Log::info("Traitement de l'expiration des rendez-vous", [
            'count' => $expiredAppointments->count(),
            'expiration_date' => $expirationDate->toDateString(),
        ]);

        foreach ($expiredAppointments as $appointment) {
            try {
                // Marquer le rendez-vous comme expiré
                $appointment->update([
                    'status' => 'expired',
                    'admin_notes' => $appointment->admin_notes . "\n\n[SYSTÈME] Rendez-vous expiré automatiquement après 30 jours sans traitement.",
                ]);

                // Envoyer la notification d'expiration
                SendAppointmentNotification::dispatch($appointment, 'expired');

                // Logger l'activité
                activity()
                    ->performedOn($appointment)
                    ->log('Rendez-vous expiré automatiquement');

                Log::info("Rendez-vous expiré avec succès", [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                ]);

            } catch (\Exception $e) {
                Log::error("Erreur lors du traitement de l'expiration", [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Job ProcessAppointmentExpiration a échoué", [
            'error' => $exception->getMessage(),
        ]);
    }
}
