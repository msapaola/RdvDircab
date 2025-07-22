<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('TestMiddleware exécuté', [
            'route' => $request->path(),
            'method' => $request->method(),
        ]);
        
        return $next($request);
    }
} 