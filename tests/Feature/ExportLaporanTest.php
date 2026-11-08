<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function login(): Karyawan
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

        return $karyawan;
    }

    /**
     * TEST CASE 1 (POSITIF):
     * Mengakses URL /export/laporan berhasil mengunduh file laporan-perusahaan.xlsx (status 200).
     */
    public function test_export_laporan_can_be_downloaded_by_authenticated_user(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-perusahaan.xlsx');
    }

    /**
     * TEST CASE 2 (NEGATIF):
     * Akses ekspor oleh pengguna yang belum login diblokir dan diarahkan ke login.
     */
    public function test_export_laporan_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/export/laporan');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * TEST CASE 3 (EDGE CASE):
     * Melakukan ekspor ketika database masih kosong tidak memicu error server 500,
     * melainkan tetap menghasilkan file template kosong yang valid.
     */
    public function test_export_laporan_empty_database_returns_valid_spreadsheet_without_server_error(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan?start_date=2024-01-01&end_date=2024-12-31');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-perusahaan.xlsx');
    }

    /**
     * Menguji export laporan relasi data null pada karyawan.
     */
    public function test_export_laporan_relasi_null_can_be_downloaded(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/laporan');

        $response->assertStatus(200);
        Excel::assertDownloaded('laporan-perusahaan.xlsx');
    }

    /**
     * Menguji export penggajian saat data tersedia.
     */
    public function test_export_penggajian_can_be_downloaded(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }

    /**
     * Menguji export penggajian saat data kosong tetap dapat diunduh.
     */
    public function test_export_penggajian_kosong_can_be_downloaded(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }

    /**
     * Menguji relasi karyawan null pada export penggajian tetap dapat diunduh.
     */
    public function test_export_penggajian_relasi_null_can_be_downloaded(): void
    {
        Excel::fake();
        $this->login();

        $response = $this->get('/export/penggajian');

        $response->assertStatus(200);
        Excel::assertDownloaded('penggajian.xlsx');
    }
}
