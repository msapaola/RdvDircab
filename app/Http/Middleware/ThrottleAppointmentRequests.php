<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAppointmentRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $email = $request->input('email');
        $maxAttempts = 5;
        $decayMinutes = 60;

        // Limite par IP
        $ipKey = 'appointment:ip:' . $ip;
        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return response()->json([
                'message' => "Trop de demandes depuis cette adresse IP. Veuillez réessayer dans " . ceil($seconds / 60) . " minutes.",
            ], 429);
        }
        RateLimiter::hit($ipKey, $decayMinutes * 60);

        // Limite par email (si fourni)
        if ($email) {
            $emailKey = 'appointment:email:' . strtolower($email);
            if (RateLimiter::tooManyAttempts($emailKey, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($emailKey);
                return response()->json([
                    'message' => "Trop de demandes pour cette adresse email. Veuillez réessayer dans " . ceil($seconds / 60) . " minutes.",
                ], 429);
            }
            RateLimiter::hit($emailKey, $decayMinutes * 60);
        }

        return $next($request);
    }
}
