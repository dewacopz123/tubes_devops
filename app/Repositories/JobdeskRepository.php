<?php 
namespace App\Repositories;

use App\Models\Jobdesk;

class JobdeskRepository
{
    public function all()
    {
        return Jobdesk::with('karyawans')->get();
    }

    public function create(array $data)
    {
        return Jobdesk::create($data);
    }

    public function update($id, array $data)
    {
        return Jobdesk::findOrFail($id)->update($data);
    }

    public function delete($id)
    {
        return Jobdesk::findOrFail($id)->delete();
    }
}
