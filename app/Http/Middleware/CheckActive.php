<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckActive
{
    public function handle(Request $request, Closure $next)
    {
        // Log simple pour vérifier que le middleware s'exécute
        Log::info('CheckActive middleware exécuté', [
            'user_id' => Auth::id(),
            'is_active' => Auth::user() ? Auth::user()->is_active : null,
            'route' => $request->path(),
        ]);
        
        $user = Auth::user();
        if ($user && !$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }
        
        return $next($request);
    }
} 