<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::byStatus('pending')->count(),
            'accepted' => Appointment::byStatus('accepted')->count(),
            'rejected' => Appointment::byStatus('rejected')->count(),
            'canceled' => Appointment::byStatus('canceled')->count() + Appointment::byStatus('canceled_by_requester')->count(),
            'expired' => Appointment::byStatus('expired')->count(),
            'completed' => Appointment::byStatus('completed')->count(),
        ];

        // Rendez-vous par mois (12 derniers mois)
        $appointmentsByMonth = Appointment::selectRaw('
                DATE_FORMAT(preferred_date, "%Y-%m") as month,
                COUNT(*) as count
            ')
            ->where('preferred_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => \Carbon\Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'count' => $item->count
                ];
            });

        // Rendez-vous par priorité
        $appointmentsByPriority = Appointment::selectRaw('
                priority,
                COUNT(*) as count
            ')
            ->groupBy('priority')
            ->orderBy('count', 'desc')
            ->get();

        // Statistiques des utilisateurs
        $userStats = User::whereIn('role', ['admin', 'assistant'])
            ->get()
            ->map(function ($user) {
                $processedAppointments = $user->processedAppointments();
                $totalProcessed = $processedAppointments->count();
                $acceptedCount = $processedAppointments->where('status', 'accepted')->count();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'processed_count' => $totalProcessed,
                    'acceptance_rate' => $totalProcessed > 0 ? round(($acceptedCount / $totalProcessed) * 100, 1) : 0,
                    'last_activity' => $user->last_activity ? $user->last_activity->diffForHumans() : 'Jamais',
                ];
            });

        // Statistiques par jour (30 derniers jours)
        $dailyStats = Appointment::selectRaw('
                DATE(preferred_date) as date,
                status,
                COUNT(*) as count
            ')
            ->where('preferred_date', '>=', now()->subDays(30))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($dayStats) {
                $stats = [];
                foreach ($dayStats as $stat) {
                    $stats[$stat->status] = $stat->count;
                }
                return $stats;
            });

        // Top des sujets de rendez-vous
        $topSubjects = Appointment::selectRaw('
                subject,
                COUNT(*) as count
            ')
            ->groupBy('subject')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Statistiques de performance
        $performanceStats = [
            'avg_response_time' => $this->getAverageResponseTime(),
            'avg_processing_time' => $this->getAverageProcessingTime(),
            'satisfaction_rate' => $this->getSatisfactionRate(),
        ];

        return Inertia::render('Admin/Statistics', [
            'stats' => $stats,
            'appointmentsByMonth' => $appointmentsByMonth,
            'appointmentsByPriority' => $appointmentsByPriority,
            'userStats' => $userStats,
            'dailyStats' => $dailyStats,
            'topSubjects' => $topSubjects,
            'performanceStats' => $performanceStats,
        ]);
    }

    private function getAverageResponseTime()
    {
        // Temps moyen entre la création et le premier traitement
        $result = Appointment::selectRaw('
                AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours
            ')
            ->whereNotNull('processed_at')
            ->first();

        return round($result->avg_hours ?? 0, 1);
    }

    private function getAverageProcessingTime()
    {
        // Temps moyen de traitement par utilisateur
        $result = DB::table('appointments')
            ->selectRaw('
                AVG(TIMESTAMPDIFF(MINUTE, created_at, processed_at)) as avg_minutes
            ')
            ->whereNotNull('processed_by')
            ->whereNotNull('processed_at')
            ->first();

        return round($result->avg_minutes ?? 0, 1);
    }

    private function getSatisfactionRate()
    {
        // Taux de satisfaction basé sur les rendez-vous acceptés vs total
        $total = Appointment::count();
        $accepted = Appointment::byStatus('accepted')->count();
        
        return $total > 0 ? round(($accepted / $total) * 100, 1) : 0;
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $appointments = Appointment::whereBetween('preferred_date', [$startDate, $endDate])
            ->with(['processedBy'])
            ->get();

        if ($format === 'csv') {
            return $this->exportToCsv($appointments);
        }

        return response()->json($appointments);
    }

    private function exportToCsv($appointments)
    {
        $filename = 'appointments_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($appointments) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            fputcsv($file, [
                'ID', 'Nom', 'Email', 'Téléphone', 'Sujet', 'Date', 'Heure', 
                'Priorité', 'Statut', 'Traité par', 'Date de traitement'
            ]);

            // Données
            foreach ($appointments as $appointment) {
                fputcsv($file, [
                    $appointment->id,
                    $appointment->name,
                    $appointment->email,
                    $appointment->phone,
                    $appointment->subject,
                    $appointment->preferred_date,
                    $appointment->preferred_time,
                    $appointment->priority,
                    $appointment->status,
                    $appointment->processedBy ? $appointment->processedBy->name : '',
                    $appointment->processed_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 