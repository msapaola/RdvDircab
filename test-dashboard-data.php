<?php

require_once 'vendor/autoload.php';

use App\Models\Appointment;
use App\Models\User;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic Dashboard Data ===\n\n";

// Check if Appointment model exists and has data
try {
    $totalAppointments = Appointment::count();
    echo "Total appointments: {$totalAppointments}\n";
    
    if ($totalAppointments > 0) {
        // Test the scopes
        $pending = Appointment::byStatus('pending')->count();
        $accepted = Appointment::byStatus('accepted')->count();
        $rejected = Appointment::byStatus('rejected')->count();
        $canceled = Appointment::byStatus('canceled')->count() + Appointment::byStatus('canceled_by_requester')->count();
        $expired = Appointment::byStatus('expired')->count();
        $completed = Appointment::byStatus('completed')->count();
        
        echo "Pending: {$pending}\n";
        echo "Accepted: {$accepted}\n";
        echo "Rejected: {$rejected}\n";
        echo "Canceled: {$canceled}\n";
        echo "Expired: {$expired}\n";
        echo "Completed: {$completed}\n";
        
        // Test statsByDay query
        $statsByDay = Appointment::selectRaw('DATE(preferred_date) as day, status, COUNT(*) as count')
            ->where('preferred_date', '>=', now()->subDays(30))
            ->groupBy('day', 'status')
            ->orderBy('day')
            ->get();
            
        echo "\nStats by day count: " . $statsByDay->count() . "\n";
        
        if ($statsByDay->count() > 0) {
            echo "Sample statsByDay data:\n";
            foreach ($statsByDay->take(3) as $stat) {
                echo "- Day: {$stat->day}, Status: {$stat->status}, Count: {$stat->count}\n";
            }
        }
        
        // Test next appointments
        $nextAppointments = Appointment::accepted()
            ->where('preferred_date', '>=', now()->toDateString())
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->limit(10)
            ->get();
            
        echo "\nNext appointments count: " . $nextAppointments->count() . "\n";
        
        if ($nextAppointments->count() > 0) {
            echo "Sample next appointment data:\n";
            $sample = $nextAppointments->first();
            echo "- ID: {$sample->id}\n";
            echo "- Name: {$sample->name}\n";
            echo "- Date: {$sample->preferred_date}\n";
            echo "- Time: {$sample->preferred_time}\n";
            echo "- Subject: {$sample->subject}\n";
            echo "- Status: {$sample->status}\n";
            echo "- Formatted Status: {$sample->formatted_status}\n";
        }
        
    } else {
        echo "No appointments found in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== End Diagnostic ===\n"; 