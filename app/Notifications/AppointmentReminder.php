<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $daysUntil;

    public function __construct(Appointment $appointment, $daysUntil = 1)
    {
        $this->appointment = $appointment;
        $this->daysUntil = $daysUntil;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $trackingUrl = route('appointments.tracking', $this->appointment->secure_token);
        
        $timeText = $this->daysUntil == 1 ? 'demain' : "dans {$this->daysUntil} jours";
        
        return (new MailMessage)
            ->subject('Rappel de votre rendez-vous - Cabinet du Gouverneur')
            ->greeting('Bonjour ' . $this->appointment->name . ',')
            ->line("Ceci est un rappel pour votre rendez-vous qui aura lieu {$timeText}.")
            ->line('')
            ->line('**Détails du rendez-vous :**')
            ->line('• Objet : ' . $this->appointment->subject)
            ->line('• Date : ' . $this->appointment->preferred_date)
            ->line('• Heure : ' . $this->appointment->preferred_time)
            ->line('• Statut : Confirmé')
            ->line('')
            ->line('**Instructions importantes :**')
            ->line('• Veuillez vous présenter 15 minutes avant l\'heure du rendez-vous')
            ->line('• Apportez une pièce d\'identité valide')
            ->line('• En cas d\'empêchement, veuillez nous contacter au plus tôt')
            ->line('• Adresse : Cabinet du Gouverneur de Kinshasa')
            ->line('')
            ->line('**En cas d\'annulation :**')
            ->line('Si vous ne pouvez pas vous présenter, vous pouvez annuler votre rendez-vous via le lien ci-dessous.')
            ->line('')
            ->action('Consulter les détails', $trackingUrl)
            ->line('')
            ->line('Nous vous remercions de votre compréhension.')
            ->salutation('Cordialement,')
            ->line('Le Cabinet du Gouverneur de Kinshasa');
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'subject' => $this->appointment->subject,
            'date' => $this->appointment->preferred_date,
            'time' => $this->appointment->preferred_time,
            'days_until' => $this->daysUntil,
            'tracking_url' => route('appointments.tracking', $this->appointment->secure_token),
        ];
    }
}
