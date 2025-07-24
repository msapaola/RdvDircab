<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentStatusUpdate extends Notification
{
    use Queueable;

    protected $appointment;
    protected $oldStatus;
    protected $adminNotes;

    public function __construct(Appointment $appointment, $oldStatus = null, $adminNotes = null)
    {
        $this->appointment = $appointment;
        $this->oldStatus = $oldStatus;
        $this->adminNotes = $adminNotes;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function logo($notifiable)
    {
        return url('/images/logohvk.webp');
    }

    public function toMail($notifiable)
    {
        $trackingUrl = route('appointments.tracking', $this->appointment->secure_token);
        $status = $this->appointment->formatted_status;

        // Formatage lisible des dates et heures
        $date = $this->appointment->preferred_date ? \Carbon\Carbon::parse($this->appointment->preferred_date)->format('d/m/Y') : '';
        $heure = $this->appointment->preferred_time ? substr($this->appointment->preferred_time, 0, 5) : '';
        
        $mailMessage = (new MailMessage)
            ->subject('Mise à jour de votre demande de rendez-vous - Cabinet du Gouverneur')
            ->greeting('Bonjour ' . $this->appointment->name . ',');

        switch ($this->appointment->status) {
            case 'accepted':
                $mailMessage
                    ->line('Nous avons le plaisir de vous informer que votre demande de rendez-vous a été **acceptée**.')
                    ->line('')
                    ->line('**Détails du rendez-vous :**')
                    ->line('• Objet : ' . $this->appointment->subject)
                    ->line('• Date : ' . $date)
                    ->line('• Heure : ' . $heure)
                    ->line('• Statut : Accepté')
                    ->line('')
                    ->line('**Instructions importantes :**')
                    ->line('• Veuillez vous présenter 15 minutes avant l\'heure du rendez-vous')
                    ->line('• Apportez une pièce d\'identité valide')
                    ->line('• En cas d\'empêchement, veuillez nous contacter au plus tôt')
                    ->line('')
                    ->action('Consulter les détails', $trackingUrl);
                break;

            case 'rejected':
                $mailMessage
                    ->line('Nous regrettons de vous informer que votre demande de rendez-vous a été **refusée**.')
                    ->line('')
                    ->line('**Détails de la demande :**')
                    ->line('• Objet : ' . $this->appointment->subject)
                    ->line('• Date souhaitée : ' . $date)
                    ->line('• Statut : Refusé')
                    ->line('')
                    ->line('**Raison du refus :**')
                    ->line($this->appointment->rejection_reason)
                    ->line('')
                    ->line('Vous pouvez soumettre une nouvelle demande si vous le souhaitez.')
                    ->action('Soumettre une nouvelle demande', route('home'));
                break;

            case 'canceled':
            case 'canceled_by_requester':
                $mailMessage
                    ->line('Votre demande de rendez-vous a été **annulée**.')
                    ->line('')
                    ->line('**Détails de la demande :**')
                    ->line('• Objet : ' . $this->appointment->subject)
                    ->line('• Date souhaitée : ' . $date)
                    ->line('• Statut : Annulé')
                    ->line('')
                    ->line('Vous pouvez soumettre une nouvelle demande à tout moment.')
                    ->action('Soumettre une nouvelle demande', route('home'));
                break;

            case 'completed':
                $mailMessage
                    ->line('Votre rendez-vous a été **terminé**.')
                    ->line('')
                    ->line('**Détails du rendez-vous :**')
                    ->line('• Objet : ' . $this->appointment->subject)
                    ->line('• Date : ' . $date)
                    ->line('• Statut : Terminé')
                    ->line('')
                    ->line('Nous vous remercions pour votre visite.')
                    ->action('Consulter l\'historique', $trackingUrl);
                break;

            default:
                $mailMessage
                    ->line('Le statut de votre demande de rendez-vous a été mis à jour.')
                    ->line('')
                    ->line('**Nouveau statut :** ' . $status)
                    ->line('')
                    ->action('Consulter les détails', $trackingUrl);
        }

        if ($this->adminNotes) {
            $mailMessage->line('')
                ->line('**Note administrative :**')
                ->line($this->adminNotes);
        }

        return $mailMessage
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->appointment->status,
            'tracking_url' => route('appointments.tracking', $this->appointment->secure_token),
        ];
    }
}
