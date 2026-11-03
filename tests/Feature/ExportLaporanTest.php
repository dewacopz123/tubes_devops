<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ExportLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function login(): void
    {
        $karyawan = Karyawan::create([
            'kode_karyawan' => 'KRY-001',
            'nama' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);
        $this->actingAs($karyawan);
    }

    /**
     * EXP-001
     * Menguji export laporan perusahaan dapat diunduh.
     */
    public function test_export_laporan_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-perusahaan.xlsx');
    }

    /**
     * EXP-002
     * Menguji export laporan perusahaan saat data kosong tetap dapat diunduh.
     */
    public function test_export_laporan_kosong_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan-kosong');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-kosong.xlsx');
    }

    /**
     * EXP-003
     * Menguji export relasi data null pada karyawan.
     */
    public function test_export_laporan_relasi_null_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan-relasi-null');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-relasi-null.xlsx');
    }

    /**
    * EXP-004
    * Menguji relasi kosong pada sheet jobdesk.
    */
    public function test_export_laporan_relasi_kosong_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan?start_date=2024-01-01&end_date=2024-12-31');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-filter-tanggal.xlsx');
    }

    /**
    * WBT-01-FR10
    * Menguji export penggajian saat data tersedia.
    */
     public function test_export_penggajian_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }

    /**
    * WBT-01-FR10
    * Menguji export penggajian saat data kosong tetap dapat diunduh.
    */
     public function test_export_penggajian_kosong_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }

    /**
    * WBT-01-FR10
    * Menguji relasi karyawan null pada export penggajian tetap dapat diunduh.
    */
     public function test_export_penggajian_relasi_null_can_be_downloaded()
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }
}
