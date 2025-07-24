<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // KPIs
        $stats = [
            'pending' => Appointment::byStatus('pending')->count(),
            'accepted' => Appointment::byStatus('accepted')->count(),
            'rejected' => Appointment::byStatus('rejected')->count(),
            'canceled' => Appointment::byStatus('canceled')->count() + Appointment::byStatus('canceled_by_requester')->count(),
            'expired' => Appointment::byStatus('expired')->count(),
            'completed' => Appointment::byStatus('completed')->count(),
        ];

        // Prochains rendez-vous acceptés
        $nextAppointments = Appointment::accepted()
            ->where('preferred_date', '>=', now()->toDateString())
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->limit(10)
            ->get();

        // Statistiques pour graphiques (exemple : nombre de RDV par statut sur 30 jours)
        $statsByDay = Appointment::selectRaw('DATE(preferred_date) as day, status, COUNT(*) as count')
            ->where('preferred_date', '>=', now()->subDays(30))
            ->groupBy('day', 'status')
            ->orderBy('day')
            ->get();

        // Rendez-vous récents avec pagination
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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'nextAppointments' => $nextAppointments,
            'statsByDay' => $statsByDay,
            'appointments' => $appointments,
            'filters' => (object) $request->only(['status', 'priority', 'date_from', 'date_to', 'search', 'sort_by', 'sort_order']),
        ]);
    }
}
