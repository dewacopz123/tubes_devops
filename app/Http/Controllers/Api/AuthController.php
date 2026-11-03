<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
                'message' => 'Email atau password salah'
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

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $user->revokeToken();
        }

        return response()->json(['message' => 'Berhasil logout']);
    }
}
