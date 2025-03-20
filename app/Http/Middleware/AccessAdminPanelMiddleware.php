<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessAdminPanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // Redirect non-admins to home
        if (!$user || $user->hasRole('admin') || $user->hasRole('super_admin') ) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}

