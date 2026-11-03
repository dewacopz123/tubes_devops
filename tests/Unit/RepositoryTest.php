<?php

namespace Tests\Unit;

use App\Models\Karyawan;
use App\Repositories\JobdeskRepository;
use App\Repositories\KaryawanRepository;
use App\Repositories\PenggajianRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(): Karyawan
    {
        return Karyawan::create([
            'kode_karyawan' => 'KRY-REP01',
            'nama' => 'Repo User',
            'email' => 'repo@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);
    }

    public function test_karyawan_repository_crud()
    {
        $repo = new KaryawanRepository;

        $created = $repo->create([
            'kode_karyawan' => 'KRY-001',
            'nama' => 'Test Karyawan',
            'email' => 'testkry@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('pass'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);
        $this->assertEquals('Test Karyawan', $created->nama);

        $found = $repo->find($created->id);
        $this->assertEquals($created->id, $found->id);

        $this->assertNotEmpty($repo->all());

        $updated = $repo->update($created->id, ['nama' => 'Test Updated']);
        $this->assertEquals('Test Updated', $updated->nama);

        $repo->delete($created->id);
        $this->assertDatabaseMissing('karyawans', ['id' => $created->id]);
    }

    public function test_jobdesk_repository_crud()
    {
        $repo = new JobdeskRepository;

        $created = $repo->create([
            'nama_jobdesk' => 'UI/UX Designer',
            'tugas_utama' => 'Merancang antarmuka sistem',
        ]);
        $this->assertEquals('UI/UX Designer', $created->nama_jobdesk);

        $this->assertNotEmpty($repo->all());

        $repo->update($created->id, ['nama_jobdesk' => 'Lead Designer']);
        $this->assertDatabaseHas('jobdesks', ['nama_jobdesk' => 'Lead Designer']);

        $repo->delete($created->id);
        $this->assertDatabaseMissing('jobdesks', ['id' => $created->id]);
    }

    public function test_penggajian_repository_crud()
    {
        $karyawan = $this->createKaryawan();
        $repo = new PenggajianRepository;

        $created = $repo->store([
            'kode_penggajian' => 'GJ-001',
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'gaji_pokok' => 4500000,
            'tunjangan' => 500000,
            'potongan' => 0,
            'metode_pembayaran' => 'Cash',
            'status' => 'Lunas',
        ]);
        $this->assertEquals(4500000, $created->gaji_pokok);

        $found = $repo->find($created->id);
        $this->assertEquals($created->id, $found->id);

        $this->assertNotEmpty($repo->getAll());

        $updated = $repo->update($created->id, ['gaji_pokok' => 5000000]);
        $this->assertEquals(5000000, $updated->gaji_pokok);

        $repo->delete($created->id);
        $this->assertDatabaseMissing('penggajians', ['id' => $created->id]);
    }
}
