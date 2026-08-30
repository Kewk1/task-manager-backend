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
     * @param  string  ...$roles  // 👈 DINAGDAG: Tumatanggap ng dynamic roles (e.g. 'admin', 'manager') galing sa routes
     */
    public function handle(Request $request, Closure $next, ...$roles): Response // 👈 BINAGO: Idinagdag ang ...$roles sa parameters
    {
        // 👈 DINAGDAG: Harang #1 - Tsek kung naka-login ang user via Sanctum
        if (!$request->user()) {
            return response([
                'status' => 'error',
                'message' => 'Unauthorized access. Please login first.'
            ], 401);
        }

        // 👈 DINAGDAG: Harang #2 - Tsek kung ang role ng naka-login na user ay kasama sa allowed $roles
        if (!in_array($request->user()->role, $roles)) {
            return response([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}