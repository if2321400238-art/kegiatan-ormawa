<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has the required role
        $roles = is_array($role) ? $role : explode('|', $role);

        if (!in_array($request->user()->role, $roles) && $request->user()->role !== 'admin') {
            abort(403, 'Tidak punya akses');
        }


        return $next($request);
    }
}
