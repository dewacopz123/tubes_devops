<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use Illuminate\Http\Request;
use App\Models\Jobdesk;
use App\Models\Karyawan;

class JobdeskController extends Controller
{
    use ValidatesSekData;

    // Tampilkan semua jobdesk
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $jobdesks = Jobdesk::with('karyawans')->get();
        } else {
            $jobdesks = Jobdesk::with('karyawans')
                ->whereHas('karyawans', function ($q) use ($user) {
                    $q->where('karyawans.id', $user->id);
                })
                ->get();
        }

        $karyawans = Karyawan::all();

        return view('Jobdesk.index', compact('jobdesks', 'karyawans'));
    }

    // Store
    public function store(Request $request)
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

    public function update(Request $request, $id)
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

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $jobdesk = Jobdesk::with('karyawans')->findOrFail($id);

        if ($user->role !== 'admin') {
            $allowed = $jobdesk->karyawans->contains('id', $user->id);

            if (!$allowed) {
                abort(403, 'Anda tidak memiliki akses ke jobdesk ini.');
            }
        }

        return $jobdesk;
    }

    public function assignForm()
    {
        $jobdesks = Jobdesk::all();
        $karyawans = Karyawan::all();
        return view('Jobdesk.assign', compact('jobdesks', 'karyawans'));
    }

    public function assign(Request $request)
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

    public function removeKaryawan($jobdeskId, $karyawanId)
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

    // Hapus jobdesk
    public function destroy($id)
    {
        Jobdesk::findOrFail($id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Jobdesk berhasil dihapus.',
        ]);
    }


    public function form()
    {
        $karyawans = Karyawan::all();
        return view('Jobdesk.formAddEdit', compact('karyawans'));
    }

}
