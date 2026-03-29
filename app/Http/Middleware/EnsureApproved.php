<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guests are fine
        if (! $user) {
            return $next($request);
        }

        // If approved, allow
        if ($user->approved_at !== null) {
            return $next($request);
        }

        // Allow these routes even if not approved
        if ($request->routeIs('pending-approval', 'logout')) {
            return $next($request);
        }

        // Optional: allow home page too
        if ($request->routeIs('home')) {
            return $next($request);
        }

        return redirect()->route('pending-approval');
    }
}
