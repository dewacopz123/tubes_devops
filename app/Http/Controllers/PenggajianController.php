<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Penggajian;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    use ValidatesSekData;

    public function index(Request $request)
{
    $user = $request->user();

    if ($user->role === 'admin') {
        $penggajians = Penggajian::with('karyawan')->get();
    } else {
        $penggajians = Penggajian::with('karyawan')
            ->where('karyawan_id', $user->id)
            ->get();
    }

    return view('Penggajian.penggajian', [
        'penggajians' => $penggajians,
        'karyawans' => Karyawan::all()
    ]);
}

    public function create()
    {
        return view('Penggajian.formAddEditPenggajian', [
            'karyawans' => Karyawan::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $tanggal = $data['tanggal'];

        if (str_contains($tanggal, '/')) {
            $tanggal = Carbon::createFromFormat('d/m/Y', $tanggal)
                ->format('Y-m-d');
        }

        $penggajian = Penggajian::create([
            'kode_penggajian' => 'PG-' . time(),
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $tanggal,
            'gaji_pokok' => $data['gaji_pokok']
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
        $data = $request->validate(
            $this->penggajianRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $penggajian = Penggajian::findOrFail($id);

        $penggajian->update([
            'karyawan_id' => $data['karyawan_id'],
            'tanggal' => $data['tanggal'],
            'gaji_pokok' => $data['gaji_pokok']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil diperbarui.',
            'data' => $penggajian->load('karyawan:id,nama'),
        ]);
    }

    public function destroy($id)
    {
        Penggajian::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data penggajian berhasil dihapus.',
        ]);
    }
}
