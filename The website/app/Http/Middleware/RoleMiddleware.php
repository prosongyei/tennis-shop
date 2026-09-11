<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // If not logged in, redirect to portal-specific login
        if (!auth()->check()) {
            if ($request->is('admin*')) {
                return redirect()->route('admin.login')->with('error', 'Admin portal login required.');
            }
            if ($request->is('pos*')) {
                return redirect()->route('pos.login')->with('error', 'Cashier terminal login required.');
            }
            return redirect()->guest(route('login'))->with('error', 'Please log in to continue.');
        }

        $user = auth()->user();

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Handle role mismatch redirects
        if ($request->is('admin*')) {
            return redirect()->route('admin.login')->with('error', 'Access denied. Admin credentials required.');
        }
        if ($request->is('pos*')) {
            return redirect()->route('pos.login')->with('error', 'Access denied. Cashier credentials required.');
        }

        abort(403, 'Unauthorized access.');
    }
}
