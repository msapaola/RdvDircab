<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentExpired extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre demande de rendez-vous a expiré - Cabinet du Gouverneur')
            ->greeting('Bonjour ' . $this->appointment->name . ',')
            ->line('Nous vous informons que votre demande de rendez-vous a expiré automatiquement.')
            ->line('')
            ->line('**Détails de la demande expirée :**')
            ->line('• Objet : ' . $this->appointment->subject)
            ->line('• Date souhaitée : ' . $this->appointment->preferred_date)
            ->line('• Heure souhaitée : ' . $this->appointment->preferred_time)
            ->line('• Statut : Expiré')
            ->line('')
            ->line('**Raison de l\'expiration :**')
            ->line('Votre demande n\'a pas pu être traitée dans le délai imparti de 30 jours.')
            ->line('')
            ->line('**Que faire maintenant ?**')
            ->line('Vous pouvez soumettre une nouvelle demande de rendez-vous à tout moment.')
            ->line('Nous vous recommandons de vérifier la disponibilité des créneaux avant de soumettre votre demande.')
            ->line('')
            ->action('Soumettre une nouvelle demande', route('home'))
            ->line('')
            ->line('Nous nous excusons pour tout désagrément causé.')
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
            'status' => 'expired',
        ];
    }
}
