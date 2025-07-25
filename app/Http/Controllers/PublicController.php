<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Notifications\AppointmentStatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Afficher la page d'accueil publique avec le calendrier
     */
    public function index()
    {
        // Récupérer les créneaux disponibles pour le mois en cours
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonths(2)->endOfMonth();
        
        $availableSlots = $this->getAvailableSlots($startDate, $endDate);
        $blockedSlots = $this->getBlockedSlots($startDate, $endDate);
        
        return Inertia::render('Public/Home', [
            'availableSlots' => $availableSlots,
            'blockedSlots' => $blockedSlots,
            'businessHours' => [
                'start' => '08:00',
                'end' => '17:00',
                'lunch_start' => '12:00',
                'lunch_end' => '14:00',
            ],
            'workingDays' => [1, 2, 3, 4, 5], // Lundi à Vendredi
        ]);
    }

    /**
     * Afficher la page de suivi d'un rendez-vous
     */
    public function tracking($token)
    {
        $appointment = Appointment::where('secure_token', $token)->first();
        
        if (!$appointment) {
            abort(404, 'Rendez-vous non trouvé');
        }

        // Empêcher l'accès aux rendez-vous expirés ou annulés
        if (in_array($appointment->status, ['expired', 'canceled', 'canceled_by_requester'])) {
            abort(403, 'Ce rendez-vous n\'est plus accessible');
        }

        // Récupérer l'historique des activités
        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Appointment::class)
            ->where('subject_id', $appointment->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Public/Tracking', [
            'appointment' => $appointment->toArray() + [
                'can_be_canceled_by_requester' => $appointment->canBeCanceledByRequester(),
                'formatted_status' => $appointment->formatted_status,
                'formatted_priority' => $appointment->formatted_priority,
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * Créer un nouveau rendez-vous
     */
    public function store(Request $request)
    {
        // Log de débogage
        \Log::info('Appointment submission attempt', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|date_format:H:i',
            'priority' => 'required|in:normal,urgent,official',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            \Log::warning('Appointment validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->except(['attachments']),
            ]);
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier le rate limiting (max 3 demandes par email par jour)
        $todayRequests = Appointment::where('email', $request->email)
            ->whereDate('created_at', today())
            ->count();

        if ($todayRequests >= 3) {
            \Log::warning('Rate limit exceeded', [
                'email' => $request->email,
                'requests_today' => $todayRequests,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Vous avez atteint la limite de 3 demandes par jour. Veuillez réessayer demain.'
            ], 429);
        }

        // Vérifier si le créneau est disponible
        $preferredDateTime = Carbon::parse($request->preferred_date . ' ' . $request->preferred_time);
        
        if (!$this->isSlotAvailable($request->preferred_date, $request->preferred_time)) {
            \Log::warning('Slot not available', [
                'date' => $request->preferred_date,
                'time' => $request->preferred_time,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ce créneau n\'est plus disponible. Veuillez choisir un autre horaire.'
            ], 422);
        }

        // Vérifier la règle des 24h (sauf urgence)
        if ($request->priority !== 'urgent') {
            $minAdvance = Carbon::now()->addDay();
            if ($preferredDateTime->lt($minAdvance)) {
                \Log::warning('Appointment too soon', [
                    'preferred_date' => $request->preferred_date,
                    'preferred_time' => $request->preferred_time,
                    'priority' => $request->priority,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Les rendez-vous doivent être demandés au moins 24h à l\'avance (sauf urgence).'
                ], 422);
            }
        }

        // Traiter les pièces jointes
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('appointments/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // Créer le rendez-vous
        $appointment = Appointment::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'priority' => $request->priority,
            'attachments' => $attachments,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        \Log::info('Appointment created successfully', [
            'appointment_id' => $appointment->id,
            'email' => $appointment->email,
            'subject' => $appointment->subject,
        ]);

        // Envoyer l'email de confirmation (sera implémenté plus tard)
        // Mail::to($appointment->email)->send(new AppointmentConfirmation($appointment));

        return response()->json([
            'success' => true,
            'message' => 'Votre demande a été soumise avec succès. Vous recevrez un email de confirmation avec un lien de suivi.',
            'tracking_url' => $appointment->tracking_url,
        ]);
    }

    /**
     * Annuler un rendez-vous (par le demandeur)
     */
    public function cancel(Request $request, $token)
    {
        $appointment = Appointment::where('secure_token', $token)->first();
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Rendez-vous non trouvé'
            ], 404);
        }

        if (!$appointment->canBeCanceledByRequester()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rendez-vous ne peut plus être annulé'
            ], 422);
        }

        // Logger l'activité avant l'annulation
        activity()
            ->performedOn($appointment)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'cancellation_method' => 'requester',
            ])
            ->log('Rendez-vous annulé par le demandeur');

        $appointment->cancelByRequester();

        // Envoyer une notification d'annulation au demandeur
        \Illuminate\Support\Facades\Notification::route('mail', $appointment->email)
            ->notify(new AppointmentStatusUpdate($appointment));

        // Envoyer notification à l'administration (optionnel)
        // Mail::to(config('mail.admin_email'))->send(new AppointmentCancellation($appointment));

        return response()->json([
            'success' => true,
            'message' => 'Votre rendez-vous a été annulé avec succès'
        ]);
    }

    /**
     * Récupérer les créneaux disponibles pour une période donnée
     */
    private function getAvailableSlots($startDate, $endDate)
    {
        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Vérifier si c'est un jour ouvré (lundi à vendredi)
            if ($currentDate->isWeekday()) {
                $daySlots = $this->generateDaySlots($currentDate);
                $slots = array_merge($slots, $daySlots);
            }
            
            $currentDate->addDay();
        }

        return $slots;
    }

    /**
     * Générer les créneaux pour une journée donnée
     */
    private function generateDaySlots($date)
    {
        $slots = [];
        $startTime = Carbon::parse('08:00');
        $endTime = Carbon::parse('17:00');
        $slotDuration = 60; // 1 heure

        $currentTime = $startTime->copy();

        while ($currentTime < $endTime) {
            // Vérifier si c'est la pause déjeuner
            if ($currentTime->format('H:i') >= '12:00' && $currentTime->format('H:i') < '14:00') {
                $currentTime->addHour();
                continue;
            }

            $slotEnd = $currentTime->copy()->addMinutes($slotDuration);
            
            // Vérifier si le créneau est disponible
            $isAvailable = $this->isSlotAvailable($date->format('Y-m-d'), $currentTime->format('H:i'));
            
            $slots[] = [
                'date' => $date->format('Y-m-d'),
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'available' => $isAvailable,
                'type' => $isAvailable ? 'available' : 'blocked',
            ];

            $currentTime->addHour();
        }

        return $slots;
    }

    /**
     * Récupérer les créneaux bloqués pour une période donnée
     */
    private function getBlockedSlots($startDate, $endDate)
    {
        // Créneaux bloqués non récurrents
        $nonRecurringSlots = BlockedSlot::nonRecurring()
            ->byDateRange($startDate, $endDate)
            ->get()
            ->map(function ($slot) {
                return [
                    'date' => $slot->date->format('Y-m-d'),
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'type' => $slot->type,
                    'reason' => $slot->reason,
                ];
            });

        // Créneaux bloqués récurrents
        $recurringSlots = BlockedSlot::generateRecurringSlots($startDate, $endDate);

        return array_merge($nonRecurringSlots->toArray(), $recurringSlots);
    }

    /**
     * Vérifier si un créneau est disponible
     */
    private function isSlotAvailable($date, $time)
    {
        $dateTime = Carbon::parse($date . ' ' . $time);
        
        // Vérifier si c'est dans le passé
        if ($dateTime->isPast()) {
            return false;
        }

        // Vérifier si c'est un jour ouvré
        if (!$dateTime->isWeekday()) {
            return false;
        }

        // Vérifier les heures d'ouverture
        $hour = (int) $dateTime->format('H');
        if ($hour < 8 || $hour >= 17) {
            return false;
        }

        // Vérifier la pause déjeuner
        if ($hour >= 12 && $hour < 14) {
            return false;
        }

        // Vérifier s'il y a déjà un rendez-vous à cette heure
        $existingAppointment = Appointment::where('preferred_date', $date)
            ->where('preferred_time', $time)
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_ACCEPTED])
            ->exists();

        if ($existingAppointment) {
            return false;
        }

        // Vérifier s'il y a un créneau bloqué (non récurrent)
        $blockedSlot = BlockedSlot::where('date', $date)
            ->where('is_recurring', false)
            ->where(function ($query) use ($time) {
                $query->where('start_time', '<=', $time)
                      ->where('end_time', '>', $time);
            })
            ->exists();

        if ($blockedSlot) {
            return false;
        }

        // Vérifier s'il y a un créneau bloqué récurrent
        $recurringBlockedSlots = BlockedSlot::recurring()->active()->get();

        foreach ($recurringBlockedSlots as $slot) {
            if ($slot->isApplicableForDate(Carbon::parse($date))) {
                $slotStart = $slot->start_time->format('H:i');
                $slotEnd = $slot->end_time->format('H:i');
                
                if ($time >= $slotStart && $time < $slotEnd) {
                    return false;
                }
            }
        }

        return true;
    }
}
