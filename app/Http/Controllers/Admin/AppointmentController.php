<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Notifications\AppointmentStatusUpdate;
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
        $oldStatus = $appointment->status;
        
        if ($appointment->accept(auth()->user())) {
            // Envoyer la notification avec le lien de suivi
            $appointment->notify(new AppointmentStatusUpdate($appointment, $oldStatus));
            
            return redirect()->back()->with('success', 'Rendez-vous accepté avec succès. Un email avec le lien de suivi a été envoyé au demandeur.');
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
            'recurrence_type' => 'nullable|in:daily,weekly,monthly',
            'recurring_until' => 'nullable|date|after:date',
        ]);

        $createData = [
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'created_by' => auth()->id(),
            'is_recurring' => $request->boolean('recurring'),
        ];

        // Gérer les champs de récurrence
        if ($request->boolean('recurring')) {
            $createData['recurrence_type'] = $request->recurrence_type ?? 'weekly';
            $createData['recurrence_end_date'] = $request->recurring_until;
        }

        $blockedSlot = BlockedSlot::create($createData);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($blockedSlot)
            ->log('Créneau bloqué créé');

        return redirect()->back()->with('success', 'Créneau bloqué créé avec succès.');
    }

    public function updateBlockedSlot(Request $request, BlockedSlot $blockedSlot)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly',
            'recurrence_end_date' => 'nullable|date|after:date',
        ]);

        $oldReason = $blockedSlot->reason;
        
        $updateData = [
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'is_recurring' => $request->boolean('is_recurring'),
        ];

        // Gérer les champs de récurrence
        if ($request->boolean('is_recurring')) {
            $updateData['recurrence_type'] = $request->recurrence_type;
            $updateData['recurrence_end_date'] = $request->recurrence_end_date;
        } else {
            $updateData['recurrence_type'] = null;
            $updateData['recurrence_end_date'] = null;
        }

        $blockedSlot->update($updateData);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($blockedSlot)
            ->log("Créneau bloqué modifié : {$oldReason} → {$request->reason}");

        return redirect()->back()->with('success', 'Créneau bloqué modifié avec succès.');
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
        $request->validate([
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id',
            'action' => 'required|in:accept,reject,cancel,complete',
            'reason' => 'nullable|string|max:500',
        ]);

        $appointments = Appointment::whereIn('id', $request->appointment_ids)->get();
        $successCount = 0;
        $errorCount = 0;

        foreach ($appointments as $appointment) {
            try {
                switch ($request->action) {
                    case 'accept':
                        if ($appointment->accept(auth()->user())) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                    
                    case 'reject':
                        if (empty($request->reason)) {
                            $errorCount++;
                        } else if ($appointment->reject(auth()->user(), $request->reason)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                    
                    case 'cancel':
                        if (empty($request->reason)) {
                            $errorCount++;
                        } else if ($appointment->cancel(auth()->user(), $request->reason)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                    
                    case 'complete':
                        if ($appointment->markAsCompleted(auth()->user())) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        $message = "Action en lot terminée : {$successCount} succès";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} erreurs";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Download an attachment from an appointment
     */
    public function downloadAttachment(Appointment $appointment, $filename)
    {
        // Vérifier que l'appointment a des pièces jointes
        if (!$appointment->hasAttachments()) {
            abort(404, 'Aucune pièce jointe trouvée.');
        }

        // Chercher le fichier dans les pièces jointes
        $attachment = null;
        foreach ($appointment->attachments as $att) {
            if ($att['name'] === $filename) {
                $attachment = $att;
                break;
            }
        }

        if (!$attachment) {
            \Log::error('Fichier non trouvé dans les pièces jointes', [
                'appointment_id' => $appointment->id,
                'requested_filename' => $filename,
                'available_attachments' => collect($appointment->attachments)->pluck('name')->toArray()
            ]);
            abort(404, 'Fichier non trouvé dans les pièces jointes.');
        }

        // Construire le chemin du fichier en utilisant le path stocké
        $filePath = storage_path('app/public/' . $attachment['path']);

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            \Log::error('Fichier non trouvé sur le serveur', [
                'appointment_id' => $appointment->id,
                'filename' => $filename,
                'file_path' => $filePath,
                'attachment_path' => $attachment['path']
            ]);
            abort(404, 'Fichier non trouvé sur le serveur.');
        }

        // Retourner le fichier pour téléchargement
        return response()->download($filePath, $attachment['name']);
    }

    /**
     * Preview an attachment from an appointment (for display in browser)
     */
    public function previewAttachment(Appointment $appointment, $filename)
    {
        // Vérifier que l'appointment a des pièces jointes
        if (!$appointment->hasAttachments()) {
            abort(404, 'Aucune pièce jointe trouvée.');
        }

        // Chercher le fichier dans les pièces jointes
        $attachment = null;
        foreach ($appointment->attachments as $att) {
            if ($att['name'] === $filename) {
                $attachment = $att;
                break;
            }
        }

        if (!$attachment) {
            \Log::error('Fichier non trouvé dans les pièces jointes pour prévisualisation', [
                'appointment_id' => $appointment->id,
                'requested_filename' => $filename,
                'available_attachments' => collect($appointment->attachments)->pluck('name')->toArray()
            ]);
            abort(404, 'Fichier non trouvé dans les pièces jointes.');
        }

        // Construire le chemin du fichier en utilisant le path stocké
        $filePath = storage_path('app/public/' . $attachment['path']);

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            \Log::error('Fichier non trouvé sur le serveur pour prévisualisation', [
                'appointment_id' => $appointment->id,
                'filename' => $filename,
                'file_path' => $filePath,
                'attachment_path' => $attachment['path']
            ]);
            abort(404, 'Fichier non trouvé sur le serveur.');
        }

        // Déterminer le type MIME
        $mimeType = $attachment['type'] ?? mime_content_type($filePath);
        
        // Retourner le fichier pour prévisualisation (sans forcer le téléchargement)
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $attachment['name'] . '"'
        ]);
    }
}
