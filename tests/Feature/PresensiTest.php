<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PresensiTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY' . str_pad((string)(Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Budi Santoso',
            'email' => 'budi' . (Karyawan::count() + 1) . '@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    private function loginAsAdmin(): Karyawan
    {
        $admin = $this->createKaryawan([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    private function loginAsKaryawan(): Karyawan
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);
        return $karyawan;
    }

    /**
     * ABS-001
     * Menguji halaman absensi dapat diakses oleh admin.
     */
    public function test_index_page_can_be_accessed_as_admin()
    {
        $this->loginAsAdmin();
        $response = $this->get('/absensi');
        $response->assertStatus(200);
    }

    /**
     * ABS-002
     * Menguji halaman absensi dapat diakses oleh karyawan.
     */
    public function test_index_page_can_be_accessed_as_karyawan()
    {
        $this->loginAsKaryawan();
        $response = $this->get('/absensi');
        $response->assertStatus(200);
    }

    /**
     * ABS-003
     * Absen masuk berhasil.
     */
    public function test_masuk_success()
    {
        $karyawan = $this->loginAsKaryawan();

        $response = $this->postJson('/absensi/masuk');

        $response->assertStatus(201);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('absensis', [
            'karyawan_id' => $karyawan->id,
            'status' => 'Masuk',
        ]);
    }

    /**
     * ABS-004
     * Absen masuk gagal ketika sudah absen hari ini.
     */
    public function test_masuk_fails_when_already_checked_in()
    {
        $karyawan = $this->loginAsKaryawan();

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'status' => 'Masuk',
        ]);

        $response = $this->postJson('/absensi/masuk');

        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);
    }

    /**
     * ABS-006
     * Absen keluar gagal ketika belum absen masuk.
     */
    public function test_keluar_fails_when_not_checked_in()
    {
        $this->loginAsKaryawan();

        $response = $this->postJson('/absensi/keluar');

        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);
    }

    /**
     * ABS-005
     * Absen keluar berhasil.
     */
    public function test_keluar_success()
    {
        $karyawan = $this->loginAsKaryawan();

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'status' => 'Masuk',
        ]);

        $response = $this->postJson('/absensi/keluar');

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('absensis', [
            'karyawan_id' => $karyawan->id,
            'status' => 'Selesai',
        ]);
    }

    /**
     * ABS-007
     * Absen keluar gagal ketika sudah absen keluar sebelumnya.
     */
    public function test_keluar_fails_when_already_checked_out()
    {
        $karyawan = $this->loginAsKaryawan();

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'status' => 'Selesai',
        ]);

        $response = $this->postJson('/absensi/keluar');

        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);
    }
}
