<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableCsrfForAppointments
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If this is the appointments route, skip CSRF verification
        if ($request->is('appointments') || $request->is('appointments/*')) {
            // Remove any CSRF token validation from the request
            $request->attributes->set('csrf_verified', true);
            
            // Also set a flag to bypass CSRF middleware
            $request->attributes->set('bypass_csrf', true);
        }
        
        return $next($request);
    }
} 