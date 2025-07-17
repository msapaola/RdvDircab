<?php

use App\Http\Controllers\PublicController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API route for appointments (no CSRF required)
Route::post('/appointments', [PublicController::class, 'store'])->name('api.appointments.store');
Route::post('/appointments/{token}/cancel', [PublicController::class, 'cancel'])->name('api.appointments.cancel');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); 