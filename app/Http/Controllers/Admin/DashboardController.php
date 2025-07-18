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
        try {
            // KPIs avec gestion d'erreur
            $stats = [
                'pending' => Appointment::where('status', 'pending')->count(),
                'accepted' => Appointment::where('status', 'accepted')->count(),
                'rejected' => Appointment::where('status', 'rejected')->count(),
                'canceled' => Appointment::whereIn('status', ['canceled', 'canceled_by_requester'])->count(),
                'expired' => Appointment::where('status', 'expired')->count(),
                'completed' => Appointment::where('status', 'completed')->count(),
            ];

            // Prochains rendez-vous acceptés
            $nextAppointments = Appointment::where('status', 'accepted')
                ->where('preferred_date', '>=', now()->toDateString())
                ->orderBy('preferred_date')
                ->orderBy('preferred_time')
                ->limit(10)
                ->get();

            // Statistiques pour graphiques
            $statsByDay = Appointment::selectRaw('DATE(preferred_date) as day, status, COUNT(*) as count')
                ->where('preferred_date', '>=', now()->subDays(30))
                ->groupBy('day', 'status')
                ->orderBy('day')
                ->get();

            // Rendez-vous récents avec pagination
            $query = Appointment::with('processedBy');

            // Filtres
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
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

        } catch (\Exception $e) {
            // En cas d'erreur, retourner des données vides
            $stats = [
                'pending' => 0,
                'accepted' => 0,
                'rejected' => 0,
                'canceled' => 0,
                'expired' => 0,
                'completed' => 0,
            ];
            $nextAppointments = collect([]);
            $statsByDay = collect([]);
            $appointments = collect([]);
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'nextAppointments' => $nextAppointments,
            'statsByDay' => $statsByDay,
            'appointments' => $appointments,
            'filters' => $request->only(['status', 'priority', 'date_from', 'date_to', 'search', 'sort_by', 'sort_order']),
        ]);
    }
}
