<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Owner has access to everything
        if ($request->user()->isOwner()) {
            return $next($request);
        }

        // Matrix of required roles vs user roles
        // If required is 'manager', user must be manager or owner.
        if ($role === 'manager' && $request->user()->isManager()) {
            return $next($request);
        }

        // If required is 'staff', everyone (who is logged in) is staff.
        if ($role === 'staff') {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden: Insufficient permissions'], 403);
    }
}
