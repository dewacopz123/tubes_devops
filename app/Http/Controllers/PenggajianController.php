<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Karyawan;
use App\Models\Penggajian;
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

        $kodePenggajian = 'PG-'.Carbon::parse($tanggal)->format('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        $penggajian = Penggajian::create([
            'kode_penggajian' => $kodePenggajian,
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
}
