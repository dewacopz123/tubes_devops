<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Karyawan;
use App\Repositories\KaryawanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    use ValidatesSekData;

    public const DEFAULT_PASSWORD = '123456';

    public function __construct(private KaryawanRepositoryInterface $karyawans) {}

    public function index()
    {
        $karyawans = Karyawan::all();

        return view('Karyawan.index', compact('karyawans'));
    }

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

        $data['kode_karyawan'] = $this->generateKodeKaryawan();
        $data['password'] = Hash::make(self::DEFAULT_PASSWORD);

        $karyawan = $this->karyawans->create($data);

        return response()->json([
            'success' => true,
            'message' => "Data karyawan berhasil ditambahkan.",
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

        if ($karyawan->is($request->user()) && $data['status'] !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
                'errors' => ['status' => ['Anda tidak dapat menonaktifkan akun Anda sendiri.']],
            ], 422);
        }

        // Akun nonaktif: cabut token API agar akses aplikasi mobile ikut terputus
        if ($data['status'] !== 'Aktif') {
            $data['api_token'] = null;
        }

        $karyawan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $karyawan,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ((int) $id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ], 403);
        }

        if (! $this->karyawans->delete($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil dihapus.',
        ]);
    }

    private function generateKodeKaryawan(): string
    {
        do {
            $kode = 'KRY-'.strtoupper(Str::random(6));
        } while (Karyawan::where('kode_karyawan', $kode)->exists());

        return $kode;
    }
}
