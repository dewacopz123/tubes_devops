<?php

namespace App\Http\Middleware;

use App\Models\Karyawan;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenMiddleware
{
    /**
     * Memvalidasi keberadaan dan keabsahan Bearer Token pada header Authorization request API.
     *
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $karyawan = Karyawan::where('api_token', $token)->first();

        if (! $karyawan) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        Auth::login($karyawan);

        return $next($request);
    }
}
