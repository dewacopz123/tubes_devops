<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Karyawan::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'kode_karyawan' => 'KRY-ADMIN',
            'nama' => 'Admin',
            'telepon' => null,
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);
    }
}
