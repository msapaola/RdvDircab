<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Traitement quotidien de l'expiration des rendez-vous (à 2h du matin)
        $schedule->command('appointments:expire')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Envoi des rappels de rendez-vous (tous les jours à 9h du matin)
        $schedule->command('appointments:send-reminders --days=1')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Envoi des rappels pour les rendez-vous dans 3 jours (tous les jours à 10h)
        $schedule->command('appointments:send-reminders --days=3')
            ->dailyAt('10:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Nettoyage des logs anciens (tous les dimanches à 3h du matin)
        $schedule->command('log:clear')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping();

        // Sauvegarde de la base de données (tous les jours à 1h du matin)
        if (config('app.env') === 'production') {
            $schedule->command('backup:run')
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 