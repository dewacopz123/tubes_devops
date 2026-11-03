<?php

namespace App\Repositories;

use App\Models\Karyawan;

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

    public function delete($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return $karyawan->delete();
    }
}
