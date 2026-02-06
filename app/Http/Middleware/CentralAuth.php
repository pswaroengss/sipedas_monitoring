<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CentralAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('auth_user')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            return redirect('/login');
        }

        return $next($request);
    }
}
