<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Notifications\AppointmentConfirmation;
use App\Notifications\AppointmentStatusUpdate;
use App\Notifications\AppointmentReminder;
use App\Notifications\AppointmentExpired;
use App\Notifications\AppointmentAccessLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointment;
    protected $notificationType;
    protected $additionalData;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(Appointment $appointment, string $notificationType, array $additionalData = [])
    {
        $this->appointment = $appointment;
        $this->notificationType = $notificationType;
        $this->additionalData = $additionalData;
    }

    public function handle()
    {
        try {
            switch ($this->notificationType) {
                case 'confirmation':
                    $this->sendConfirmation();
                    break;
                case 'status_update':
                    $this->sendStatusUpdate();
                    break;
                case 'reminder':
                    $this->sendReminder();
                    break;
                case 'expired':
                    $this->sendExpired();
                    break;
                case 'access_link':
                    $this->sendAccessLink();
                    break;
                default:
                    Log::warning("Type de notification inconnu: {$this->notificationType}", [
                        'appointment_id' => $this->appointment->id,
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de notification", [
                'appointment_id' => $this->appointment->id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function sendConfirmation()
    {
        $notification = new AppointmentConfirmation($this->appointment);
        $this->appointment->notify($notification);
        
        Log::info("Notification de confirmation envoyée", [
            'appointment_id' => $this->appointment->id,
            'email' => $this->appointment->email,
        ]);
    }

    protected function sendStatusUpdate()
    {
        $oldStatus = $this->additionalData['old_status'] ?? null;
        $adminNotes = $this->additionalData['admin_notes'] ?? null;
        
        $notification = new AppointmentStatusUpdate($this->appointment, $oldStatus, $adminNotes);
        $this->appointment->notify($notification);
        
        Log::info("Notification de mise à jour de statut envoyée", [
            'appointment_id' => $this->appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $this->appointment->status,
            'email' => $this->appointment->email,
        ]);
    }

    protected function sendReminder()
    {
        $daysUntil = $this->additionalData['days_until'] ?? 1;
        
        $notification = new AppointmentReminder($this->appointment, $daysUntil);
        $this->appointment->notify($notification);
        
        Log::info("Notification de rappel envoyée", [
            'appointment_id' => $this->appointment->id,
            'days_until' => $daysUntil,
            'email' => $this->appointment->email,
        ]);
    }

    protected function sendExpired()
    {
        $notification = new AppointmentExpired($this->appointment);
        $this->appointment->notify($notification);
        
        Log::info("Notification d'expiration envoyée", [
            'appointment_id' => $this->appointment->id,
            'email' => $this->appointment->email,
        ]);
    }

    protected function sendAccessLink()
    {
        $notification = new AppointmentAccessLink($this->appointment);
        $this->appointment->notify($notification);
        
        Log::info("Notification de lien d'accès envoyée", [
            'appointment_id' => $this->appointment->id,
            'email' => $this->appointment->email,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Job SendAppointmentNotification a échoué", [
            'appointment_id' => $this->appointment->id,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage(),
        ]);
    }
}
