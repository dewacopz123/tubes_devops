<?php

namespace App\Repositories;

use App\Models\Penggajian;

class PenggajianRepository implements PenggajianRepositoryInterface
{
    public function getAll()
    {
        return Penggajian::with('karyawan')->get();
    }

    public function find($id)
    {
        return Penggajian::with('karyawan')->findOrFail($id);
    }

    public function store(array $data)
    {
        return Penggajian::create($data);
    }

    public function update($id, array $data)
    {
        $penggajian = Penggajian::findOrFail($id);
        $penggajian->update($data);
        return $penggajian;
    }

    public function delete($id)
    {
        return Penggajian::destroy($id);
    }
}
