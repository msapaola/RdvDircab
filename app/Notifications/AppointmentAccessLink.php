<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentAccessLink extends Notification implements ShouldQueue
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
        $accessUrl = route('appointments.access', $this->appointment->secure_token);
        
        return (new MailMessage)
            ->subject('Accès à votre demande de rendez-vous - Cabinet du Gouverneur')
            ->greeting('Bonjour ' . $this->appointment->name . ',')
            ->line('Voici votre lien d\'accès personnel pour consulter et gérer votre demande de rendez-vous.')
            ->line('')
            ->line('**Détails de votre demande :**')
            ->line('• Objet : ' . $this->appointment->subject)
            ->line('• Date souhaitée : ' . $this->appointment->preferred_date)
            ->line('• Heure souhaitée : ' . $this->appointment->preferred_time)
            ->line('• Statut actuel : ' . $this->appointment->formatted_status)
            ->line('')
            ->line('**Ce que vous pouvez faire :**')
            ->line('• Consulter le statut de votre demande')
            ->line('• Voir les détails complets')
            ->line('• Annuler votre demande si nécessaire')
            ->line('• Recevoir les mises à jour')
            ->line('')
            ->action('Accéder à ma demande', $accessUrl)
            ->line('')
            ->line('**Informations de sécurité :**')
            ->line('• Ce lien est personnel et sécurisé')
            ->line('• Ne partagez pas ce lien avec d\'autres personnes')
            ->line('• Le lien reste valide tant que votre demande est active')
            ->line('')
            ->line('**En cas de problème :**')
            ->line('Si vous ne pouvez pas accéder à votre demande, veuillez nous contacter.')
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
            'access_url' => route('appointments.access', $this->appointment->secure_token),
        ];
    }
}
