<?php

namespace App\Repositories;

use App\Models\Karyawan;
use Illuminate\Support\Facades\DB;

class KaryawanRepository implements KaryawanRepositoryInterface
{
    public function all()
    {
        return Karyawan::all();
    }

    public function find($id)
    {
        return Karyawan::findOrFail($id);
    }

    public function create(array $data)
    {
        return Karyawan::create($data);
    }

    public function update($id, array $data)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update($data);

        return $karyawan;
    }

    /**
     * Hapus karyawan beserta relasi pivot jobdesk-nya.
     * Mengembalikan false (bukan exception) jika data sudah tidak ada.
     */
    public function delete($id)
    {
        $karyawan = Karyawan::find($id);

        if (! $karyawan) {
            return false;
        }

        return DB::transaction(function () use ($karyawan) {
            $karyawan->jobdesks()->detach();

            return (bool) $karyawan->delete();
        });
    }
}
