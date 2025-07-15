<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Routes publiques
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/tracking/{token}', [PublicController::class, 'tracking'])->name('appointments.tracking');
Route::post('/appointments', [PublicController::class, 'store'])
    ->middleware('throttle.appointments')
    ->name('appointments.store');
Route::post('/appointments/{token}/cancel', [PublicController::class, 'cancel'])->name('appointments.cancel');

// Route de bienvenue (redirige vers l'accueil public)
Route::get('/welcome', function () {
    return redirect()->route('home');
})->name('welcome');

// Routes de test
Route::get('/test', [TestController::class, 'index'])->name('test');
Route::get('/test-charts', [TestController::class, 'charts'])->name('test.charts');
Route::get('/test-colors', [TestController::class, 'colors'])->name('test.colors');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
