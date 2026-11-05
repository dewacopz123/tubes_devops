<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresensiTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Budi Santoso',
            'email' => 'budi'.(Karyawan::count() + 1).'@example.com',
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

    /**
     * ABS-012
     * Absen keluar ditolak ketika status hari ini sudah 'Selesai'
     * meskipun jam keluar belum tercatat (data tidak konsisten).
     */
    public function test_keluar_fails_when_status_is_already_selesai()
    {
        $karyawan = $this->loginAsKaryawan();

        Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'status' => 'Selesai',
        ]);

        $response = $this->postJson('/absensi/keluar');

        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseHas('absensis', [
            'karyawan_id' => $karyawan->id,
            'jam_keluar' => null,
        ]);
    }

    /**
     * ABS-008
     * Absen masuk ditolak untuk karyawan berstatus Nonaktif.
     */
    public function test_masuk_fails_when_karyawan_is_nonaktif()
    {
        $karyawan = $this->createKaryawan([
            'nama' => 'Karyawan Nonaktif',
            'email' => 'nonaktif@example.com',
            'status' => 'Nonaktif',
        ]);
        $this->actingAs($karyawan);

        $response = $this->postJson('/absensi/masuk');

        $response->assertStatus(403);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseCount('absensis', 0);
    }

    /**
     * ABS-009
     * Admin melihat rekap absensi seluruh karyawan.
     */
    public function test_index_admin_can_see_all_absensi()
    {
        $this->loginAsAdmin();

        $budi = $this->createKaryawan([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
        $siti = $this->createKaryawan([
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
        ]);

        Absensi::create([
            'karyawan_id' => $budi->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'status' => 'Masuk',
        ]);

        Absensi::create([
            'karyawan_id' => $siti->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:15:00',
            'status' => 'Masuk',
        ]);

        $response = $this->get('/absensi');

        $response->assertStatus(200);
        $response->assertViewHas('absensis', function ($absensis) {
            return $absensis->count() === 2;
        });
        $response->assertSee('Budi Santoso');
        $response->assertSee('Siti Aminah');
    }

    /**
     * ABS-010
     * Karyawan biasa hanya melihat riwayat absensi miliknya sendiri.
     */
    public function test_index_karyawan_only_sees_own_absensi()
    {
        $budi = $this->createKaryawan([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);
        $siti = $this->createKaryawan([
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
        ]);
        $this->actingAs($budi);

        Absensi::create([
            'karyawan_id' => $budi->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:00:00',
            'status' => 'Masuk',
        ]);

        Absensi::create([
            'karyawan_id' => $siti->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '08:15:00',
            'status' => 'Masuk',
        ]);

        $response = $this->get('/absensi');

        $response->assertStatus(200);
        $response->assertViewHas('absensis', function ($absensis) {
            return $absensis->count() === 1;
        });
        $response->assertSee('budi@example.com');
        $response->assertDontSee('siti@example.com');
    }

    /**
     * ABS-011
     * Halaman rekap menampilkan empty state saat belum ada data absensi.
     */
    public function test_index_shows_empty_state_when_no_absensi()
    {
        $this->loginAsAdmin();

        $response = $this->get('/absensi');

        $response->assertStatus(200);
        $response->assertViewHas('absensis', function ($absensis) {
            return $absensis->count() === 0;
        });
        $response->assertSee('Belum ada data absensi.');
    }
}
