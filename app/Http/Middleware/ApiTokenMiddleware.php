<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Auth;

class ApiTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $karyawan = Karyawan::where('api_token', $token)->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Auth::login($karyawan);

        return $next($request);
    }
}
