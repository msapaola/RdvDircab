<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmation extends Notification implements ShouldQueue
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
        $trackingUrl = route('appointments.tracking', $this->appointment->secure_token);
        
        return (new MailMessage)
            ->subject('Confirmation de votre demande de rendez-vous - Cabinet du Gouverneur')
            ->greeting('Bonjour ' . $this->appointment->name . ',')
            ->line('Nous avons bien reçu votre demande de rendez-vous auprès du Cabinet du Gouverneur de Kinshasa.')
            ->line('Voici un résumé de votre demande :')
            ->line('')
            ->line('**Objet :** ' . $this->appointment->subject)
            ->line('**Date souhaitée :** ' . $this->appointment->preferred_date)
            ->line('**Heure souhaitée :** ' . $this->appointment->preferred_time)
            ->line('**Priorité :** ' . $this->appointment->formatted_priority)
            ->line('**Statut actuel :** En attente de traitement')
            ->line('')
            ->line('Votre demande sera examinée par nos services dans les plus brefs délais.')
            ->line('Vous recevrez une notification dès que votre rendez-vous sera traité.')
            ->line('')
            ->action('Suivre ma demande', $trackingUrl)
            ->line('Vous pouvez également annuler votre demande à tout moment via le lien ci-dessus.')
            ->line('')
            ->line('**Informations importantes :**')
            ->line('• Ce lien est personnel et sécurisé')
            ->line('• Conservez cet email pour accéder à votre demande')
            ->line('• Les demandes expirent automatiquement après 30 jours')
            ->line('')
            ->line('Pour toute question, veuillez nous contacter.')
            ->salutation('Cordialement,')
            ->line('Le Cabinet du Gouverneur de Kinshasa');
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'subject' => $this->appointment->subject,
            'status' => $this->appointment->status,
            'tracking_url' => route('appointments.tracking', $this->appointment->secure_token),
        ];
    }
}
