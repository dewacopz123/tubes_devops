<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama dengan ringkasan agregasi metrik statistik real-time:
     * - total seluruh karyawan
     * - total karyawan aktif dan nonaktif
     * - jumlah karyawan hadir hari ini (status Masuk / Selesai)
     * - daftar karyawan beserta status jobdesk
     *
     * @return View
     */
    public function index()
    {
        $today = Carbon::today();
        $hasAbsensisTable = Schema::hasTable('absensis');
        $hasJobdesksTable = Schema::hasTable('jobdesks');

        $hadirHariIni = $hasAbsensisTable
            ? Absensi::whereDate('tanggal', $today)
                ->whereIn('status', ['Masuk', 'Selesai'])
                ->distinct('karyawan_id')
                ->count('karyawan_id')
            : 0;

        $karyawans = $hasJobdesksTable
            ? Karyawan::with('jobdesks')->get()
            : Karyawan::all();

        return view('Dashboard.index', [
            'totalKaryawan' => Karyawan::count(),
            'karyawanAktif' => Karyawan::where('status', 'Aktif')->count(),
            'karyawanNonaktif' => Karyawan::where('status', 'Nonaktif')->count(),
            'hadirHariIni' => $hadirHariIni,
            'karyawanPunyaJobdesk' => $hasJobdesksTable
                ? Karyawan::has('jobdesks')->count()
                : 0,
            'karyawanTanpaJobdesk' => $hasJobdesksTable
                ? Karyawan::doesntHave('jobdesks')->count()
                : Karyawan::count(),
            'karyawans' => $karyawans,
        ]);
    }
}
