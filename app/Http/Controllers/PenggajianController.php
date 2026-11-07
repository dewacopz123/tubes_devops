<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Penggajian;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PenggajianController extends Controller
{
    use ValidatesSekData;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $penggajians = Penggajian::with('karyawan')->get();
            $karyawans = Karyawan::all();
        } else {
            $penggajians = Penggajian::with('karyawan')
                ->where('karyawan_id', $user->id)
                ->get();
            $karyawans = Karyawan::where('id', $user->id)->get();
        }

        return view('Penggajian.penggajian', [
            'penggajians' => $penggajians,
            'karyawans' => $karyawans,
        ]);
    }

    public function create(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat membuka form penggajian.');
        }

        return view('Penggajian.formAddEditPenggajian', [
            'karyawans' => Karyawan::all(),
        ]);
    }

    /**
     * Hitung bonus kehadiran otomatis berbasis data absensi status 'Selesai' pada bulan & tahun penggajian.
     * Insentif: Rp 50.000 per kehadiran yang tuntas.
     */
    public function hitungBonusOtomatis(int $karyawanId, string $tanggal): float
    {
        $date = Carbon::parse($tanggal);

        $jumlahKehadiran = Absensi::where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $date->month)
            ->whereYear('tanggal', $date->year)
            ->where('status', 'Selesai')
            ->count();

        return (float) ($jumlahKehadiran * 50000);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat menambahkan data penggajian.');
        }

        if ($request->filled('tanggal') && str_contains($request->input('tanggal'), '/')) {
            try {
                $request->merge([
                    'tanggal' => Carbon::createFromFormat('d/m/Y', $request->input('tanggal'))->format('Y-m-d'),
                ]);
            } catch (\Throwable $e) {
                // Biarkan validator menangani error format jika tanggal tidak valid
            }
        }

        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $tanggal = $data['tanggal'];

        // Kalkulasi bonus: jika bonus tidak diisi manual atau dicentang otomatis, hitung dari absensi
        if ($request->boolean('hitung_otomatis_bonus') || ! array_key_exists('bonus', $data) || $data['bonus'] === null) {
            $bonus = $this->hitungBonusOtomatis((int) $data['karyawan_id'], $tanggal);
        } else {
            $bonus = (float) $data['bonus'];
        }

        $totalGaji = (float) $data['gaji_pokok'] + $bonus;

        $kodePenggajian = 'PG-'.Carbon::parse($tanggal)->format('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        $penggajian = Penggajian::create([
            'kode_penggajian' => $kodePenggajian,
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $tanggal,
            'gaji_pokok' => $data['gaji_pokok'],
            'bonus' => $bonus,
            'total_gaji' => $totalGaji,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil ditambahkan.',
            'data' => $penggajian->load('karyawan:id,nama'),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $penggajian = Penggajian::findOrFail($id);

        if ($user->role !== 'admin' && $penggajian->karyawan_id != $user->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return $penggajian;
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat memperbarui data penggajian.');
        }

        if ($request->filled('tanggal') && str_contains($request->input('tanggal'), '/')) {
            try {
                $request->merge([
                    'tanggal' => Carbon::createFromFormat('d/m/Y', $request->input('tanggal'))->format('Y-m-d'),
                ]);
            } catch (\Throwable $e) {
                // Biarkan validator menangani error format jika tanggal tidak valid
            }
        }

        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $penggajian = Penggajian::findOrFail($id);

        $tanggal = $data['tanggal'];

        if ($request->boolean('hitung_otomatis_bonus') || (! array_key_exists('bonus', $data) && $penggajian->bonus === null)) {
            $bonus = $this->hitungBonusOtomatis((int) $data['karyawan_id'], $tanggal);
        } elseif (array_key_exists('bonus', $data) && $data['bonus'] !== null) {
            $bonus = (float) $data['bonus'];
        } else {
            $bonus = (float) ($penggajian->bonus ?? 0);
        }

        $totalGaji = (float) $data['gaji_pokok'] + $bonus;

        $penggajian->update([
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $tanggal,
            'gaji_pokok' => $data['gaji_pokok'],
            'bonus' => $bonus,
            'total_gaji' => $totalGaji,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil diperbarui.',
            'data' => $penggajian->load('karyawan:id,nama'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat menghapus data penggajian.');
        }

        Penggajian::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil dihapus.',
        ]);
    }

    /**
     * Rekapitulasi ringkasan finansial & statistik penggajian.
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $totalSlip = Penggajian::count();
            $totalGajiPokok = (float) Penggajian::sum('gaji_pokok');
            $totalBonus = (float) Penggajian::sum('bonus');
            $totalPengeluaran = (float) Penggajian::sum('total_gaji');
            if ($totalPengeluaran == 0 && $totalSlip > 0) {
                $totalPengeluaran = $totalGajiPokok + $totalBonus;
            }
            $rataRataGaji = $totalSlip > 0 ? round($totalPengeluaran / $totalSlip, 2) : 0;

            return response()->json([
                'success' => true,
                'role' => 'admin',
                'summary' => [
                    'total_slip_diterbitkan' => $totalSlip,
                    'total_gaji_pokok' => $totalGajiPokok,
                    'total_bonus_dibagikan' => $totalBonus,
                    'total_pengeluaran_gaji' => $totalPengeluaran,
                    'rata_rata_gaji' => $rataRataGaji,
                ],
            ]);
        }

        $totalSlip = Penggajian::where('karyawan_id', $user->id)->count();
        $totalGajiPokok = (float) Penggajian::where('karyawan_id', $user->id)->sum('gaji_pokok');
        $totalBonus = (float) Penggajian::where('karyawan_id', $user->id)->sum('bonus');
        $totalDiterima = (float) Penggajian::where('karyawan_id', $user->id)->sum('total_gaji');
        if ($totalDiterima == 0 && $totalSlip > 0) {
            $totalDiterima = $totalGajiPokok + $totalBonus;
        }
        $rataRataGaji = $totalSlip > 0 ? round($totalDiterima / $totalSlip, 2) : 0;

        return response()->json([
            'success' => true,
            'role' => 'karyawan',
            'summary' => [
                'total_slip' => $totalSlip,
                'total_gaji_pokok' => $totalGajiPokok,
                'total_bonus_diterima' => $totalBonus,
                'total_pendapatan' => $totalDiterima,
                'rata_rata_gaji' => $rataRataGaji,
            ],
        ]);
    }

    /**
     * Download dokumen PDF resmi Slip Gaji Karyawan.
     */
    public function downloadPdf(Request $request, $id)
    {
        $user = $request->user();

        $penggajian = Penggajian::with('karyawan')->findOrFail($id);

        if ($user->role !== 'admin' && $penggajian->karyawan_id != $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengunduh slip gaji ini.');
        }

        $totalGaji = $penggajian->total_gaji > 0
            ? (float) $penggajian->total_gaji
            : (float) ($penggajian->gaji_pokok + ($penggajian->bonus ?? 0));

        $pdf = Pdf::loadView('Penggajian.slip_pdf', [
            'penggajian' => $penggajian,
            'totalGaji' => $totalGaji,
        ]);

        return $pdf->download("Slip-Gaji-{$penggajian->kode_penggajian}.pdf");
    }
}
