<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenggajianTest extends TestCase
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
            'email' => 'admin'.(Karyawan::count() + 1).'@example.com',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    private function loginAsKaryawan(array $attributes = []): Karyawan
    {
        $karyawan = $this->createKaryawan(array_merge([
            'role' => 'karyawan',
        ], $attributes));
        $this->actingAs($karyawan);

        return $karyawan;
    }

    private function createPenggajian(array $attributes = []): Penggajian
    {
        $karyawanId = $attributes['karyawan_id'] ?? $this->createKaryawan()->id;

        return Penggajian::create(array_merge([
            'kode_penggajian' => 'PG-'.uniqid(),
            'karyawan_id' => $karyawanId,
            'tanggal' => '2025-01-01',
            'gaji_pokok' => 5000000,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR 1: Kalkulator / Input Penggajian & Auto-Generated Code
    |--------------------------------------------------------------------------
    */

    /**
     * PG-001: Store data penggajian berhasil dengan kode auto-generate.
     */
    public function test_store_penggajian_success()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-01-15',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['success' => true]);

        $penggajian = Penggajian::where('karyawan_id', $karyawan->id)->first();
        $this->assertNotNull($penggajian);
        $this->assertStringStartsWith('PG-', $penggajian->kode_penggajian);
        $this->assertEquals('2025-01-15', $penggajian->tanggal);
        $this->assertEquals(5000000, $penggajian->gaji_pokok);
    }

    /**
     * PG-002: Store data penggajian dengan format tanggal bergaris miring (d/m/Y).
     */
    public function test_store_penggajian_with_slash_date_format()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '25/01/2025',
            'gaji_pokok' => 7500000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('penggajians', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-01-25',
            'gaji_pokok' => 7500000,
        ]);
    }

    /**
     * PG-003: Store gagal ketika nominal gaji pokok bernilai negatif.
     */
    public function test_store_fails_when_gaji_pokok_negative()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-01-15',
            'gaji_pokok' => -500000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('gaji_pokok');
    }

    /**
     * PG-004: Store gagal ketika karyawan tidak ditemukan (ID fiktif).
     */
    public function test_store_fails_when_karyawan_not_found()
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => 99999,
            'tanggal' => '2025-01-15',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('karyawan_id');
    }

    /**
     * PG-005: Store gagal ketika tanggal tidak valid.
     */
    public function test_store_fails_when_tanggal_invalid()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => 'invalid-date',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tanggal');
    }

    /**
     * PG-006: Store gagal ketika dilakukan oleh karyawan biasa (403 Forbidden).
     */
    public function test_store_fails_as_karyawan()
    {
        $karyawan = $this->loginAsKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-01-15',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(403);
    }

    /**
     * PG-007: Halaman form tambah penggajian dapat diakses oleh admin.
     */
    public function test_create_page_can_be_accessed_as_admin()
    {
        $this->loginAsAdmin();
        $response = $this->get('/penggajian/create');
        $response->assertStatus(200);
    }

    /**
     * PG-008: Halaman form tambah penggajian ditolak untuk karyawan biasa (403 Forbidden).
     */
    public function test_create_page_fails_as_karyawan()
    {
        $this->loginAsKaryawan();
        $response = $this->get('/penggajian/create');
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR 2: Proteksi Kerahasiaan Slip Gaji Mandiri Karyawan
    |--------------------------------------------------------------------------
    */

    /**
     * PG-009: Halaman index penggajian dapat diakses oleh admin dan menampilkan semua slip.
     */
    public function test_index_page_can_be_accessed_as_admin_and_shows_all_records()
    {
        $this->loginAsAdmin();
        $karyawan1 = $this->createKaryawan(['nama' => 'Karyawan Satu']);
        $karyawan2 = $this->createKaryawan(['nama' => 'Karyawan Dua']);

        $this->createPenggajian(['karyawan_id' => $karyawan1->id, 'gaji_pokok' => 4000000]);
        $this->createPenggajian(['karyawan_id' => $karyawan2->id, 'gaji_pokok' => 5000000]);

        $response = $this->get('/penggajian');
        $response->assertStatus(200);
        $response->assertSee('Karyawan Satu');
        $response->assertSee('Karyawan Dua');
    }

    /**
     * PG-010: Halaman index penggajian sebagai karyawan hanya menampilkan slip miliknya.
     */
    public function test_index_page_can_be_accessed_as_karyawan_and_only_shows_own_record()
    {
        $me = $this->loginAsKaryawan(['nama' => 'Karyawan Mandiri']);
        $other = $this->createKaryawan(['nama' => 'Karyawan Rahasia']);

        $this->createPenggajian(['karyawan_id' => $me->id, 'gaji_pokok' => 4500000]);
        $this->createPenggajian(['karyawan_id' => $other->id, 'gaji_pokok' => 9900000]);

        $response = $this->get('/penggajian');
        $response->assertStatus(200);
        $response->assertSee('Karyawan Mandiri');
        $response->assertDontSee('Karyawan Rahasia');
    }

    /**
     * PG-011: Show data penggajian berhasil sebagai admin.
     */
    public function test_show_penggajian_success_as_admin()
    {
        $this->loginAsAdmin();
        $penggajian = $this->createPenggajian();

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $penggajian->id]);
    }

    /**
     * PG-012: Show data penggajian berhasil sebagai pemilik data (karyawan owner).
     */
    public function test_show_penggajian_success_as_owner()
    {
        $karyawan = $this->loginAsKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $penggajian->id]);
    }

    /**
     * PG-013: Show gagal ketika karyawan mencoba melihat slip gaji orang lain (403 Forbidden).
     */
    public function test_show_penggajian_fails_as_non_owner()
    {
        $this->loginAsKaryawan();
        $otherKaryawan = $this->createKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $otherKaryawan->id]);

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(403);
    }

    /**
     * PG-014: Show gagal ketika ID penggajian tidak ditemukan (404 Not Found).
     */
    public function test_show_non_existing_penggajian()
    {
        $this->loginAsAdmin();
        $response = $this->get('/penggajian/99999');
        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR 3: Pembaruan & Penghapusan Slip Gaji oleh Admin
    |--------------------------------------------------------------------------
    */

    /**
     * PG-015: Update data penggajian berhasil sebagai admin.
     */
    public function test_update_penggajian_success()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->putJson("/penggajian/{$penggajian->id}", [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-02-01',
            'gaji_pokok' => 6000000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('penggajians', [
            'id' => $penggajian->id,
            'gaji_pokok' => 6000000,
            'tanggal' => '2025-02-01',
        ]);
    }

    /**
     * PG-016: Update data penggajian dengan format tanggal bergaris miring (d/m/Y).
     */
    public function test_update_penggajian_with_slash_date()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->putJson("/penggajian/{$penggajian->id}", [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '10/02/2025',
            'gaji_pokok' => 6500000,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('penggajians', [
            'id' => $penggajian->id,
            'tanggal' => '2025-02-10',
            'gaji_pokok' => 6500000,
        ]);
    }

    /**
     * PG-017: Update gagal ketika dilakukan oleh karyawan biasa (403 Forbidden).
     */
    public function test_update_penggajian_fails_as_karyawan()
    {
        $karyawan = $this->loginAsKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->putJson("/penggajian/{$penggajian->id}", [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-02-01',
            'gaji_pokok' => 6000000,
        ]);

        $response->assertStatus(403);
    }

    /**
     * PG-018: Update gagal ketika nominal gaji pokok bernilai negatif.
     */
    public function test_update_fails_when_gaji_pokok_negative()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->putJson("/penggajian/{$penggajian->id}", [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-02-01',
            'gaji_pokok' => -100000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('gaji_pokok');
    }

    /**
     * PG-019: Update gagal ketika ID penggajian tidak ditemukan (404 Not Found).
     */
    public function test_update_non_existing_penggajian()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->putJson('/penggajian/99999', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-02-01',
            'gaji_pokok' => 6000000,
        ]);

        $response->assertStatus(404);
    }

    /**
     * PG-020: Delete data penggajian berhasil sebagai admin.
     */
    public function test_destroy_penggajian_success()
    {
        $this->loginAsAdmin();
        $penggajian = $this->createPenggajian();

        $response = $this->delete("/penggajian/{$penggajian->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseMissing('penggajians', ['id' => $penggajian->id]);
    }

    /**
     * PG-021: Delete gagal ketika dilakukan oleh karyawan biasa (403 Forbidden).
     */
    public function test_destroy_penggajian_fails_as_karyawan()
    {
        $karyawan = $this->loginAsKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->delete("/penggajian/{$penggajian->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('penggajians', ['id' => $penggajian->id]);
    }

    /**
     * PG-022: Delete gagal ketika ID penggajian tidak ditemukan (404 Not Found).
     */
    public function test_destroy_non_existing_penggajian()
    {
        $this->loginAsAdmin();
        $response = $this->delete('/penggajian/99999');
        $response->assertStatus(404);
    }
}
