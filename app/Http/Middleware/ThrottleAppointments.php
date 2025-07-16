<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAppointments
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Limiter à 5 demandes par IP par heure
        $key = 'appointments:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de demandes. Veuillez réessayer dans ' . RateLimiter::availableIn($key) . ' secondes.'
            ], 429);
        }
        
        RateLimiter::hit($key, 3600); // 1 heure
        
        return $next($request);
    }
}
