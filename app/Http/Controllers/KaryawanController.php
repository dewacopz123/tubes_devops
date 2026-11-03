<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    use ValidatesSekData;

    public function index()
    {
        $karyawans = Karyawan::all();
        return view('Karyawan.index', compact('karyawans'));
    }

    // 🔥 TAMBAHKAN INI
    public function form()
    {
        return view('Karyawan.form');
    }

    public function show($id)
    {
        return response()->json(
            Karyawan::findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->karyawanRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $data['kode_karyawan'] = 'KRY-' . strtoupper(Str::random(6));
        $data['password'] = Hash::make('123456');

        $karyawan = Karyawan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil ditambahkan.',
            'data' => $karyawan,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $data = $request->validate(
            $this->karyawanRules((int) $id),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $karyawan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $karyawan,
        ]);
    }

    public function destroy($id)
    {
        Karyawan::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil dihapus.',
        ]);
    }
}
