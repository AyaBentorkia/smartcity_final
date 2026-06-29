<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Si pas de header Authorization mais cookie présent
       if (!$request->bearerToken()) {
    $token = $_COOKIE['access_token'] ?? null;
    
    if ($token) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }
}

        return $next($request);
    }
}