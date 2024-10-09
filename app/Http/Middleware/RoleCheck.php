<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, $role = null): Response
    {
        if (auth()->user() && auth()->user()->role == $role) {
            // Lanjutkan request ke middleware berikutnya atau controller
            return $next($request);
        }

        // Jika user tidak memiliki role yang sesuai, return response 403 Forbidden
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to access this resource.'
        ], 403);
    }
}
