<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActive
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('CheckActive middleware exécuté', [
            'user_id' => optional(Auth::user())->id,
            'is_active' => optional(Auth::user())->is_active,
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