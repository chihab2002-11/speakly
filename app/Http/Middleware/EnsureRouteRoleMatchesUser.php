<?php

namespace App\Http\Middleware;

use App\Support\DashboardRedirector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRouteRoleMatchesUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeRole = (string) $request->route('role');
        $user = $request->user();

        if (! $user || $routeRole === '') {
            abort(403);
        }

        if (! $user->hasRole($routeRole) || DashboardRedirector::roleFor($user) !== $routeRole) {
            abort(403);
        }

        return $next($request);
    }
}
