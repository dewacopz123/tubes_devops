<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Jobdesk;
use App\Models\Absensi;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SyncController extends Controller
{
    use ValidatesSekData;

    public function karyawans()
    {
        return Karyawan::select(['id', 'kode_karyawan', 'nama', 'email', 'telepon', 'role', 'status', 'created_at', 'updated_at'])->get();
    }

    public function showKaryawan($id)
    {
        return response()->json([
            'success' => true,
            'data' => Karyawan::findOrFail($id),
        ]);
    }

    public function storeKaryawan(Request $request)
    {
        $data = $request->validate(
            $this->karyawanRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $data['kode_karyawan'] = 'KRY-' . strtoupper(Str::random(6));
        $data['password'] = Hash::make($request->input('password', '123456'));

        $karyawan = Karyawan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil ditambahkan.',
            'data' => $karyawan,
        ], 201);
    }

    public function updateKaryawan(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $data = $request->validate(
            $this->karyawanRules((int) $id),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $karyawan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $karyawan,
        ]);
    }

    public function destroyKaryawan($id)
    {
        Karyawan::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil dihapus.',
        ]);
    }

    public function jobdesks()
    {
        return Jobdesk::with('karyawans:id,nama')->orderBy('id', 'desc')->get();
    }

    public function showJobdesk($id)
    {
        return response()->json([
            'success' => true,
            'data' => Jobdesk::with('karyawans:id,nama')->findOrFail($id),
        ]);
    }

    public function absensis()
    {
        return Absensi::with('karyawan:id,nama')->orderBy('tanggal', 'desc')->get();
    }

    public function showAbsensi($id)
    {
        return response()->json([
            'success' => true,
            'data' => Absensi::with('karyawan:id,nama,email')->findOrFail($id),
        ]);
    }

    public function storeAbsensi(Request $request)
    {
        $data = $request->validate(
            $this->absensiRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $absensi = Absensi::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil ditambahkan.',
            'data' => $absensi->load('karyawan:id,nama,email'),
        ], 201);
    }

    public function updateAbsensi(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);
        $data = $request->validate(
            $this->absensiRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $absensi->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui.',
            'data' => $absensi->load('karyawan:id,nama,email'),
        ]);
    }

    /**
     * Assign jobdesk ke satu atau banyak karyawan
     */
    public function assignJobdesk(Request $request)
    {
        $data = $request->validate(
            [
                'jobdesk_id' => ['required', 'exists:jobdesks,id'],
                'karyawan_id' => ['required', 'array', 'min:1'],
                'karyawan_id.*' => ['required', 'exists:karyawans,id'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $jobdesk = Jobdesk::findOrFail($data['jobdesk_id']);

        $existingIds = $jobdesk->karyawans()
            ->pluck('karyawans.id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $duplicateIds = array_intersect(
            $existingIds,
            array_map('strval', $data['karyawan_id'])
        );

        if (!empty($duplicateIds)) {
            $duplicateNames = Karyawan::whereIn('id', $duplicateIds)
                ->pluck('nama')
                ->implode(', ');

            return response()->json([
                'success' => false,
                'message' => 'Beberapa karyawan sudah ditugaskan ke jobdesk ini: ' . $duplicateNames,
            ], 422);
        }

        $jobdesk->karyawans()->syncWithoutDetaching($data['karyawan_id']);

        return response()->json([
            'success' => true,
            'message' => 'Jobdesk berhasil diassign ke karyawan.',
            'data' => $jobdesk->load('karyawans:id,nama'),
        ]);
    }

    /**
     * Hapus karyawan dari jobdesk
     */
    public function removeJobdeskKaryawan($jobdeskId, $karyawanId)
    {
        $jobdesk = Jobdesk::findOrFail($jobdeskId);
        $karyawan = Karyawan::findOrFail($karyawanId);

        $isAssigned = $jobdesk->karyawans()
            ->where('karyawans.id', $karyawanId)
            ->exists();

        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak terdaftar pada jobdesk ini.',
            ], 404);
        }

        $jobdesk->karyawans()->detach($karyawan->id);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dihapus dari jobdesk.',
            'data' => $jobdesk->load('karyawans:id,nama'),
        ]);
    }

    public function destroyAbsensi($id)
    {
        Absensi::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus.',
        ]);
    }

    public function penggajians()
    {
        return Penggajian::with('karyawan:id,nama')->orderBy('tanggal', 'desc')->get();
    }

    public function showPenggajian($id)
    {
        return response()->json([
            'success' => true,
            'data' => Penggajian::with('karyawan:id,nama')->findOrFail($id),
        ]);
    }

    public function absensiMasuk(Request $request)
    {
        $karyawanId = Auth::id();
        $tanggal = Carbon::today();

        $exists = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah absen hari ini',
            ], 400);
        }

        $absensi = Absensi::create([
            'karyawan_id' => $karyawanId,
            'tanggal' => $tanggal,
            'jam_masuk' => now()->format('H:i:s'),
            'status' => 'Masuk',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen masuk kerja.',
            'data' => $absensi,
        ], 201);
    }

    public function absensiKeluar(Request $request)
    {
        $karyawanId = Auth::id();
        $tanggal = Carbon::today();

        $absensi = Absensi::where('karyawan_id', $karyawanId)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Belum absen masuk',
            ], 400);
        }

        if ($absensi->jam_keluar) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah absen keluar',
            ], 400);
        }

        $absensi->update([
            'jam_keluar' => now()->format('H:i:s'),
            'status' => 'Selesai',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil absen selesai kerja.',
            'data' => $absensi,
        ]);
    }

    public function storeJobdesk(Request $request)
    {
        $data = $request->validate(
            $this->jobdeskRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $jobdesk = Jobdesk::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Jobdesk berhasil ditambahkan.',
            'data' => $jobdesk->load('karyawans:id,nama'),
        ], 201);
    }

    public function updateJobdesk(Request $request, $id)
    {
        $data = $request->validate(
            $this->jobdeskRules($id),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $jobdesk = Jobdesk::findOrFail($id);
        $jobdesk->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Jobdesk berhasil diperbarui.',
            'data' => $jobdesk->load('karyawans:id,nama'),
        ]);
    }

    public function destroyJobdesk($id)
    {
        Jobdesk::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Jobdesk berhasil dihapus.',
        ]);
    }

    public function storePenggajian(Request $request)
    {
        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $tanggal = $data['tanggal'];
        if (str_contains($tanggal, '/')) {
            $tanggal = Carbon::createFromFormat('d/m/Y', $tanggal)->format('Y-m-d');
        }

        $penggajian = Penggajian::create([
            'kode_penggajian' => 'PG-' . time(),
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $tanggal,
            'gaji_pokok' => $data['gaji_pokok'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil ditambahkan.',
            'data' => $penggajian->load('karyawan:id,nama'),
        ], 201);
    }

    public function updatePenggajian(Request $request, $id)
    {
        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $penggajian = Penggajian::findOrFail($id);
        $penggajian->update([
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $data['tanggal'],
            'gaji_pokok' => $data['gaji_pokok'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil diperbarui.',
            'data' => $penggajian->load('karyawan:id,nama'),
        ]);
    }

    public function destroyPenggajian($id)
    {
        Penggajian::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil dihapus.',
        ]);
    }

    public function uploadFoto(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if (
            $karyawan->foto &&
            Storage::disk('public')->exists($karyawan->foto)
        ) {

            Storage::disk('public')->delete($karyawan->foto);
        }

        $path = $request->file('foto')
            ->store('karyawan', 'public');

        $karyawan->update([
            'foto' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'data' => [
                'foto' => $path,
                'foto_url' => asset('storage/' . $path)
            ]
        ]);
    }
}
