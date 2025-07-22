<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }
        return $next($request);
    }
} 