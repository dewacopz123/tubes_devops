<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureKaryawanAktif
{
    public const MESSAGE = 'Akun Anda tidak aktif';

    /**
     * Memutus sesi karyawan yang statusnya diubah menjadi Nonaktif
     * saat masih login, sehingga aksesnya langsung terkunci.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->status !== 'Aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => self::MESSAGE,
                ], 403);
            }

            return redirect('/login')
                ->withErrors(['email' => self::MESSAGE])
                ->with('active', 'login');
        }

        return $next($request);
    }
}
