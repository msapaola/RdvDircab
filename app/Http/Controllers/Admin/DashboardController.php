<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
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

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'nextAppointments' => $nextAppointments,
            'statsByDay' => $statsByDay,
        ]);
    }
}
