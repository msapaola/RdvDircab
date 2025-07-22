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
        // Log ultra-simple pour diagnostiquer
        Log::info('CheckActive: Début du middleware');
        
        try {
            $user = Auth::user();
            Log::info('CheckActive: User trouvé', ['user_id' => $user ? $user->id : null]);
            
            if ($user && !$user->is_active) {
                Log::info('CheckActive: User inactif, déconnexion');
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Votre compte a été désactivé.']);
            }
            
            Log::info('CheckActive: User actif ou pas connecté');
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('CheckActive: Erreur', ['error' => $e->getMessage()]);
            return $next($request);
        }
    }
} 