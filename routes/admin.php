<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin|assistant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Appointments management
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::post('/{appointment}/accept', [AppointmentController::class, 'accept'])->name('accept');
        Route::post('/{appointment}/reject', [AppointmentController::class, 'reject'])->name('reject');
        Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
    });
    
    // Users management (admin only)
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Blocked slots management
    Route::prefix('blocked-slots')->name('blocked-slots.')->group(function () {
        Route::get('/', [AppointmentController::class, 'blockedSlots'])->name('index');
        Route::post('/', [AppointmentController::class, 'storeBlockedSlot'])->name('store');
        Route::delete('/{blockedSlot}', [AppointmentController::class, 'destroyBlockedSlot'])->name('destroy');
    });
}); 