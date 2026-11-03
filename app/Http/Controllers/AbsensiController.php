<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Http\Controllers\Concerns\ValidatesSekData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    use ValidatesSekData;

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $absensis = Absensi::with('karyawan')
                ->orderBy('tanggal', 'desc')
                ->get();
        } else {
            $absensis = Absensi::with('karyawan')
                ->where('karyawan_id', $user->id)
                ->orderBy('tanggal', 'desc')
                ->get();
        }

        return view('Absensi.index', compact('absensis'));
    }

    public function masuk()
    {
        $user = Auth::user();
        $karyawan_id = $user->id;
        $tanggal = Carbon::today();

        $cek = Absensi::where('karyawan_id', $karyawan_id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($cek) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal absen masuk. Anda sudah absen hari ini.',
            ], 400);
        }

        $absensi = Absensi::create([
            'karyawan_id' => $karyawan_id,
            'tanggal' => $tanggal,
            'jam_masuk' => now()->format('H:i:s'),
            'status' => 'Masuk'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen masuk kerja.',
            'data' => $absensi,
        ], 201);
    }
    public function keluar()
    {
        $user = Auth::user();
        $karyawan_id = $user->id;
        $tanggal = Carbon::today();

        $absensi = Absensi::where('karyawan_id', $karyawan_id)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal absen keluar. Anda belum absen masuk.',
            ], 400);
        }

        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal absen keluar. Anda sudah absen keluar.',
            ], 400);
        }

        $absensi->update([
            'jam_keluar' => now()->format('H:i:s'),
            'status' => 'Selesai'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen selesai kerja.',
            'data' => $absensi,
        ]);
    }
}
