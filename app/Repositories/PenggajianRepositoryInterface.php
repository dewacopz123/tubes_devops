<?php

namespace App\Repositories;

interface PenggajianRepositoryInterface
{
    public function getAll();
    public function find($id);
    public function store(array $data);
    public function update($id, array $data);
    public function delete($id);
}
