<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireApiToken
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure the request is authenticated via a Personal Access Token, not just session cookies
        $user = $request->user();
        if (!$user || !$user->currentAccessToken()) {
            abort(401, 'API token required');
        }
        return $next($request);
    }
}

