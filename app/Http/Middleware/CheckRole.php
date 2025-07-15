<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Accès refusé');
        }

        // Si un seul rôle est passé sous forme de chaîne
        if (count($roles) === 1 && is_string($roles[0]) && str_contains($roles[0], '|')) {
            $roles = explode('|', $roles[0]);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
        }

        return $next($request);
    }
}
