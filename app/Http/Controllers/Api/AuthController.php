<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Otentikasi akun karyawan dan generate Bearer token unik 80 karakter untuk integrasi REST API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $karyawan = Karyawan::where('email', $request->email)
            ->where('status', 'Aktif')
            ->first();

        if (! $karyawan || ! Hash::check($request->password, $karyawan->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $karyawan->createToken();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $karyawan->id,
                'kode_karyawan' => $karyawan->kode_karyawan,
                'nama' => $karyawan->nama,
                'email' => $karyawan->email,
                'telepon' => $karyawan->telepon,
                'role' => $karyawan->role,
                'status' => $karyawan->status,
            ],
        ]);
    }

    /**
     * Logout akun dari sesi REST API dan revoke token dari database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $user->revokeToken();
        }

        return response()->json([
            'message' => 'Berhasil logout',
        ]);
    }
}
