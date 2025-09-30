<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For API requests, return 403 JSON; for web, redirect
        $isAdmin = $request->user() && ($request->user()->is_admin ?? false);
        if (!$isAdmin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(403, 'Unauthorized');
            }
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }
        return $next($request);
    }
}
