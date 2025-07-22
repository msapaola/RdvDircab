<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthAndActive
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('AuthAndActive: Début du middleware');
        
        // Vérifier l'authentification
        if (!Auth::check()) {
            Log::info('AuthAndActive: Pas authentifié, redirection vers login');
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        Log::info('AuthAndActive: User trouvé', ['user_id' => $user->id, 'is_active' => $user->is_active]);
        
        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            Log::info('AuthAndActive: User inactif, déconnexion');
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte a été désactivé.']);
        }
        
        Log::info('AuthAndActive: User authentifié et actif');
        return $next($request);
    }
} 