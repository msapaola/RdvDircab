<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('processedBy');

        // Filtres
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('date_from')) {
            $query->where('preferred_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('preferred_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $appointments = $query->paginate(15)->withQueryString();

        // Statistiques pour les filtres
        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::pending()->count(),
            'accepted' => Appointment::accepted()->count(),
            'rejected' => Appointment::byStatus('rejected')->count(),
            'canceled' => Appointment::byStatus('canceled')->count() + Appointment::byStatus('canceled_by_requester')->count(),
            'expired' => Appointment::byStatus('expired')->count(),
            'completed' => Appointment::byStatus('completed')->count(),
        ];

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'date_from', 'date_to', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    public function show(Appointment $appointment)
    {
        $appointment->load('processedBy');
        
        // Récupérer l'historique des activités
        $activities = \Spatie\Activitylog\Models\Activity::where('subject_type', Appointment::class)
            ->where('subject_id', $appointment->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Appointments/Show', [
            'appointment' => $appointment,
            'activities' => $activities,
        ]);
    }

    public function accept(Request $request, Appointment $appointment)
    {
        if ($appointment->accept(auth()->user())) {
            return redirect()->back()->with('success', 'Rendez-vous accepté avec succès.');
        }

        return redirect()->back()->with('error', 'Impossible d\'accepter ce rendez-vous.');
    }

    public function reject(Request $request, Appointment $appointment)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($appointment->reject(auth()->user(), $request->rejection_reason)) {
            return redirect()->back()->with('success', 'Rendez-vous refusé avec succès.');
        }

        return redirect()->back()->with('error', 'Impossible de refuser ce rendez-vous.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($appointment->cancel(auth()->user(), $request->admin_notes)) {
            return redirect()->back()->with('success', 'Rendez-vous annulé avec succès.');
        }

        return redirect()->back()->with('error', 'Impossible d\'annuler ce rendez-vous.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|date_format:H:i',
            'priority' => 'required|in:normal,urgent,official',
            'status' => 'required|in:pending,accepted,rejected,canceled,completed,expired',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'priority' => $request->priority,
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($appointment)
            ->log('Rendez-vous modifié');

        return redirect()->back()->with('success', 'Rendez-vous modifié avec succès.');
    }

    public function complete(Request $request, Appointment $appointment)
    {
        if ($appointment->markAsCompleted(auth()->user())) {
            return redirect()->back()->with('success', 'Rendez-vous marqué comme terminé.');
        }

        return redirect()->back()->with('error', 'Impossible de marquer ce rendez-vous comme terminé.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointmentName = $appointment->name;
        $appointment->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Rendez-vous supprimé : {$appointmentName}");

        return redirect()->back()->with('success', 'Rendez-vous supprimé avec succès.');
    }

    // Gestion des créneaux bloqués
    public function blockedSlots(Request $request)
    {
        $query = BlockedSlot::query();

        // Filtres
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('reason')) {
            $query->where('reason', 'like', "%{$request->reason}%");
        }

        // Tri
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $blockedSlots = $query->paginate(15)->withQueryString();

        // Statistiques
        $stats = [
            'total' => BlockedSlot::count(),
            'this_month' => BlockedSlot::whereMonth('date', now()->month)->count(),
            'next_month' => BlockedSlot::whereMonth('date', now()->addMonth()->month)->count(),
        ];

        return Inertia::render('Admin/BlockedSlots/Index', [
            'blockedSlots' => $blockedSlots,
            'stats' => $stats,
            'filters' => $request->only(['date_from', 'date_to', 'reason', 'sort_by', 'sort_order']),
        ]);
    }

    public function storeBlockedSlot(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
            'recurring' => 'boolean',
            'recurring_until' => 'nullable|date|after:date',
        ]);

        $blockedSlot = BlockedSlot::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'created_by' => auth()->id(),
        ]);

        // Gérer la récurrence si activée
        if ($request->recurring && $request->recurring_until) {
            $currentDate = \Carbon\Carbon::parse($request->date);
            $endDate = \Carbon\Carbon::parse($request->recurring_until);
            
            while ($currentDate->addWeek() <= $endDate) {
                BlockedSlot::create([
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'reason' => $request->reason . ' (récurrent)',
                    'created_by' => auth()->id(),
                ]);
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($blockedSlot)
            ->log('Créneau bloqué créé');

        return redirect()->back()->with('success', 'Créneau bloqué créé avec succès.');
    }

    public function destroyBlockedSlot(BlockedSlot $blockedSlot)
    {
        $reason = $blockedSlot->reason;
        $blockedSlot->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Créneau bloqué supprimé : {$reason}");

        return redirect()->back()->with('success', 'Créneau bloqué supprimé avec succès.');
    }

    public function bulkAction(Request $request)
    {
        // Log pour déboguer
        \Log::info('Bulk action request received', [
            'action' => $request->action,
            'appointment_ids' => $request->appointment_ids,
            'reason' => $request->reason ?? 'none',
            'user' => auth()->user()->email
        ]);

        $request->validate([
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id',
            'action' => 'required|in:accept,reject,cancel,complete',
            'reason' => 'nullable|string|max:500',
        ]);

        $appointments = Appointment::whereIn('id', $request->appointment_ids)->get();
        $successCount = 0;
        $errorCount = 0;

        \Log::info('Processing bulk action', [
            'appointments_count' => $appointments->count(),
            'action' => $request->action
        ]);

        foreach ($appointments as $appointment) {
            try {
                switch ($request->action) {
                    case 'accept':
                        if ($appointment->accept(auth()->user())) {
                            $successCount++;
                            \Log::info('Appointment accepted', ['id' => $appointment->id]);
                        } else {
                            $errorCount++;
                            \Log::error('Failed to accept appointment', ['id' => $appointment->id]);
                        }
                        break;
                    
                    case 'reject':
                        if (empty($request->reason)) {
                            $errorCount++;
                            \Log::error('Reject action requires a reason', ['id' => $appointment->id]);
                        } else if ($appointment->reject(auth()->user(), $request->reason)) {
                            $successCount++;
                            \Log::info('Appointment rejected', ['id' => $appointment->id]);
                        } else {
                            $errorCount++;
                            \Log::error('Failed to reject appointment', ['id' => $appointment->id]);
                        }
                        break;
                    
                    case 'cancel':
                        if (empty($request->reason)) {
                            $errorCount++;
                            \Log::error('Cancel action requires a reason', ['id' => $appointment->id]);
                        } else if ($appointment->cancel(auth()->user(), $request->reason)) {
                            $successCount++;
                            \Log::info('Appointment canceled', ['id' => $appointment->id]);
                        } else {
                            $errorCount++;
                            \Log::error('Failed to cancel appointment', ['id' => $appointment->id]);
                        }
                        break;
                    
                    case 'complete':
                        if ($appointment->markAsCompleted(auth()->user())) {
                            $successCount++;
                            \Log::info('Appointment completed', ['id' => $appointment->id]);
                        } else {
                            $errorCount++;
                            \Log::error('Failed to complete appointment', ['id' => $appointment->id]);
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error('Exception in bulk action', [
                    'appointment_id' => $appointment->id,
                    'action' => $request->action,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $message = "Action en lot terminée : {$successCount} succès";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} erreurs";
        }

        \Log::info('Bulk action completed', [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'message' => $message
        ]);

        return redirect()->back()->with('success', $message);
    }
}
