<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:admin|assistant'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - Accessible par admin et assistant
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Appointments management - Accessible par admin et assistant
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/test', function () {
            return Inertia::render('Admin/Appointments/TestIndex', [
                'appointments' => \App\Models\Appointment::paginate(10),
                'stats' => [
                    'total' => \App\Models\Appointment::count(),
                    'pending' => \App\Models\Appointment::where('status', 'pending')->count(),
                    'accepted' => \App\Models\Appointment::where('status', 'accepted')->count(),
                    'rejected' => \App\Models\Appointment::where('status', 'rejected')->count(),
                    'canceled' => \App\Models\Appointment::whereIn('status', ['canceled', 'canceled_by_requester'])->count(),
                    'completed' => \App\Models\Appointment::where('status', 'completed')->count(),
                ],
                'filters' => request()->all()
            ]);
        })->name('test');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
        Route::post('/{appointment}/reject', [AppointmentController::class, 'reject'])->name('reject');
        Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::post('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [AppointmentController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{appointment}/attachments/{filename}', [AppointmentController::class, 'downloadAttachment'])->name('attachments.download');
        Route::get('/{appointment}/attachments/{filename}/preview', [AppointmentController::class, 'previewAttachment'])->name('attachments.preview');
        
        // Export des rendez-vous - Admin seulement
        Route::get('/export/csv', [AppointmentController::class, 'exportCsv'])->name('export.csv')->middleware('role:admin');
        Route::get('/export/excel', [AppointmentController::class, 'exportExcel'])->name('export.excel')->middleware('role:admin');
    });
    
    // Blocked slots management - Accessible par admin et assistant
    Route::prefix('blocked-slots')->name('blocked-slots.')->group(function () {
        Route::get('/', [AppointmentController::class, 'blockedSlots'])->name('index');
        Route::post('/', [AppointmentController::class, 'storeBlockedSlot'])->name('store');
        Route::put('/{blockedSlot}', [AppointmentController::class, 'updateBlockedSlot'])->name('update');
        Route::delete('/{blockedSlot}', [AppointmentController::class, 'destroyBlockedSlot'])->name('destroy');
    });
    
    // Users management - Admin seulement
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Statistics - Admin seulement
    Route::prefix('statistics')->name('statistics.')->middleware('role:admin')->group(function () {
        Route::get('/', [StatisticsController::class, 'index'])->name('index');
        Route::get('/export', [StatisticsController::class, 'export'])->name('export');
        Route::get('/performance', [StatisticsController::class, 'performance'])->name('performance');
        Route::get('/reports', [StatisticsController::class, 'reports'])->name('reports');
    });
    
    // Settings - Admin seulement
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::patch('/', [SettingsController::class, 'update'])->name('update');
        Route::patch('/hours', [SettingsController::class, 'updateHours'])->name('hours');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/restore', [SettingsController::class, 'restore'])->name('restore');
    });
    
    // System management - Admin seulement
    Route::prefix('system')->name('system.')->middleware('role:admin')->group(function () {
        Route::get('/logs', function () {
            return Inertia::render('Admin/System/Logs');
        })->name('logs');
        
        Route::get('/cache', function () {
            return Inertia::render('Admin/System/Cache');
        })->name('cache');
        
        Route::post('/cache/clear', function () {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            return redirect()->back()->with('success', 'Cache vidé avec succès.');
        })->name('cache.clear');
        
        Route::get('/maintenance', function () {
            return Inertia::render('Admin/System/Maintenance');
        })->name('maintenance');
        
        Route::post('/maintenance/toggle', function () {
            $isDown = app()->isDownForMaintenance();
            if ($isDown) {
                \Artisan::call('up');
                $message = 'Mode maintenance désactivé.';
            } else {
                \Artisan::call('down', ['--secret' => 'admin-secret']);
                $message = 'Mode maintenance activé.';
            }
            return redirect()->back()->with('success', $message);
        })->name('maintenance.toggle');
    });
    
    // Profile management - Accessible par admin et assistant
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Admin/Profile/Index');
        })->name('index');
        
        Route::get('/edit', function () {
            return Inertia::render('Admin/Profile/Edit');
        })->name('edit');
        
        Route::patch('/', function () {
            // Logique de mise à jour du profil
        })->name('update');
    });
}); 