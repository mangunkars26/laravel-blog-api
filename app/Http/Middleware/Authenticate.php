<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
        // return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle unauthenticated requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, array $guards)
    {
        // Kembalikan respons JSON jika tidak terautentikasi
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
