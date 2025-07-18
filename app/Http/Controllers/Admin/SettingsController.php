<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Cache::remember('admin_settings', 3600, function () {
            return [
                'business_name' => config('app.business_name', 'Cabinet du Gouverneur'),
                'business_email' => config('app.business_email', 'contact@gouvernorat-kinshasa.cd'),
                'business_phone' => config('app.business_phone', '+243 123 456 789'),
                'business_address' => config('app.business_address', 'Kinshasa, RDC'),
                'max_appointments_per_day' => config('app.max_appointments_per_day', 20),
                'appointment_duration' => config('app.appointment_duration', 30),
                'advance_booking_days' => config('app.advance_booking_days', 30),
                'auto_expire_days' => config('app.auto_expire_days', 7),
                'enable_email_notifications' => config('app.enable_email_notifications', true),
                'enable_sms_notifications' => config('app.enable_sms_notifications', false),
            ];
        });

        $businessHours = Cache::remember('business_hours', 3600, function () {
            return [
                'start_time' => config('app.business_start_time', '08:00'),
                'end_time' => config('app.business_end_time', '17:00'),
                'lunch_start' => config('app.lunch_start_time', '12:00'),
                'lunch_end' => config('app.lunch_end_time', '14:00'),
            ];
        });

        $workingDays = Cache::remember('working_days', 3600, function () {
            return config('app.working_days', [1, 2, 3, 4, 5]); // Lundi à Vendredi
        });

        return Inertia::render('Admin/Settings', [
            'settings' => $settings,
            'businessHours' => $businessHours,
            'workingDays' => $workingDays,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'required|string|max:500',
            'max_appointments_per_day' => 'required|integer|min:1|max:100',
            'appointment_duration' => 'required|integer|min:15|max:120',
            'advance_booking_days' => 'required|integer|min:1|max:365',
            'auto_expire_days' => 'required|integer|min:1|max:30',
            'enable_email_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
        ]);

        // Sauvegarder dans la base de données ou le cache
        foreach ($validated as $key => $value) {
            $this->saveSetting($key, $value);
        }

        // Vider le cache
        Cache::forget('admin_settings');

        // Logger l'activité
        activity()
            ->performedOn(auth()->user())
            ->withProperties([
                'settings_updated' => array_keys($validated),
                'ip_address' => $request->ip(),
            ])
            ->log('Paramètres mis à jour');

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function updateHours(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'lunch_start' => 'required|date_format:H:i',
            'lunch_end' => 'required|date_format:H:i|after:lunch_start',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|in:0,1,2,3,4,5,6',
        ]);

        // Sauvegarder les horaires
        $this->saveSetting('business_start_time', $validated['start_time']);
        $this->saveSetting('business_end_time', $validated['end_time']);
        $this->saveSetting('lunch_start_time', $validated['lunch_start']);
        $this->saveSetting('lunch_end_time', $validated['lunch_end']);
        $this->saveSetting('working_days', $validated['working_days']);

        // Vider le cache
        Cache::forget('business_hours');
        Cache::forget('working_days');

        // Logger l'activité
        activity()
            ->performedOn(auth()->user())
            ->withProperties([
                'hours_updated' => $validated,
                'ip_address' => $request->ip(),
            ])
            ->log('Horaires mis à jour');

        return redirect()->back()->with('success', 'Horaires mis à jour avec succès.');
    }

    public function backup()
    {
        // Créer une sauvegarde des paramètres
        $settings = [
            'general' => $this->getAllSettings(),
            'business_hours' => $this->getBusinessHours(),
            'working_days' => $this->getWorkingDays(),
            'backup_date' => now()->toISOString(),
        ];

        $filename = 'settings_backup_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $backup = json_decode($request->file('backup_file')->get(), true);
            
            if (isset($backup['general'])) {
                foreach ($backup['general'] as $key => $value) {
                    $this->saveSetting($key, $value);
                }
            }

            if (isset($backup['business_hours'])) {
                foreach ($backup['business_hours'] as $key => $value) {
                    $this->saveSetting($key, $value);
                }
            }

            if (isset($backup['working_days'])) {
                $this->saveSetting('working_days', $backup['working_days']);
            }

            // Vider tous les caches
            Cache::forget('admin_settings');
            Cache::forget('business_hours');
            Cache::forget('working_days');

            // Logger l'activité
            activity()
                ->performedOn(auth()->user())
                ->withProperties([
                    'backup_restored' => $backup['backup_date'] ?? 'unknown',
                    'ip_address' => $request->ip(),
                ])
                ->log('Paramètres restaurés depuis une sauvegarde');

            return redirect()->back()->with('success', 'Paramètres restaurés avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la restauration: ' . $e->getMessage());
        }
    }

    private function saveSetting($key, $value)
    {
        // Ici vous pouvez sauvegarder dans une table settings ou utiliser le cache
        // Pour cet exemple, on utilise le cache
        Cache::put("setting_{$key}", $value, 86400); // 24 heures
        
        // Optionnel: sauvegarder dans une table settings
        // DB::table('settings')->updateOrInsert(
        //     ['key' => $key],
        //     ['value' => is_array($value) ? json_encode($value) : $value, 'updated_at' => now()]
        // );
    }

    private function getAllSettings()
    {
        return [
            'business_name' => Cache::get('setting_business_name', config('app.business_name')),
            'business_email' => Cache::get('setting_business_email', config('app.business_email')),
            'business_phone' => Cache::get('setting_business_phone', config('app.business_phone')),
            'business_address' => Cache::get('setting_business_address', config('app.business_address')),
            'max_appointments_per_day' => Cache::get('setting_max_appointments_per_day', config('app.max_appointments_per_day')),
            'appointment_duration' => Cache::get('setting_appointment_duration', config('app.appointment_duration')),
            'advance_booking_days' => Cache::get('setting_advance_booking_days', config('app.advance_booking_days')),
            'auto_expire_days' => Cache::get('setting_auto_expire_days', config('app.auto_expire_days')),
            'enable_email_notifications' => Cache::get('setting_enable_email_notifications', config('app.enable_email_notifications')),
            'enable_sms_notifications' => Cache::get('setting_enable_sms_notifications', config('app.enable_sms_notifications')),
        ];
    }

    private function getBusinessHours()
    {
        return [
            'start_time' => Cache::get('setting_business_start_time', config('app.business_start_time')),
            'end_time' => Cache::get('setting_business_end_time', config('app.business_end_time')),
            'lunch_start' => Cache::get('setting_lunch_start_time', config('app.lunch_start_time')),
            'lunch_end' => Cache::get('setting_lunch_end_time', config('app.lunch_end_time')),
        ];
    }

    private function getWorkingDays()
    {
        return Cache::get('setting_working_days', config('app.working_days', [1, 2, 3, 4, 5]));
    }
} 