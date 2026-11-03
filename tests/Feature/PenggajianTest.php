<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use App\Models\Penggajian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PenggajianTest extends TestCase
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

    private function createPenggajian(array $attributes = []): Penggajian
    {
        $karyawanId = $attributes['karyawan_id'] ?? $this->createKaryawan()->id;

        return Penggajian::create(array_merge([
            'kode_penggajian' => 'PG-' . uniqid(),
            'karyawan_id' => $karyawanId,
            'tanggal' => '2025-01-01',
            'gaji_pokok' => 5000000,
        ], $attributes));
    }

     /**
     * PG-004
     * Store data penggajian berhasil.
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
        $this->assertDatabaseHas('penggajians', [
            'karyawan_id' => $karyawan->id,
            'gaji_pokok' => 5000000,
        ]);
    }


    /**
     * PG-002
     * Menguji halaman penggajian dapat diakses oleh karyawan.
     */
    public function test_index_page_can_be_accessed_as_karyawan()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);
        $response = $this->get('/penggajian');
        $response->assertStatus(200);
    }

    /**
     * PG-003
     * Menguji halaman form tambah penggajian dapat diakses.
     */
    public function test_create_page_can_be_accessed()
    {
        $this->loginAsAdmin();
        $response = $this->get('/penggajian/create');
        $response->assertStatus(200);
    }

   

    /**
     * PG-005
     * Store gagal ketika karyawan tidak ditemukan.
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
     * PG-007
     * Store gagal ketika tanggal valid.
     */
    public function test_store_fails_when_tanggal_invalid()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '01-15-2025',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tanggal');
    }

    /**
     * PG-007
     * Store gagal ketika tanggal valid.
     */
    public function test_store_fails_when_tanggal_valid()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/penggajian', [
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2025-13-01',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tanggal');
    }

    /**
     * PG-008
     * Show data penggajian berhasil sebagai admin.
     */
    public function test_show_penggajian_success_as_admin()
    {
        $this->loginAsAdmin();
        $penggajian = $this->createPenggajian();

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(200);
    }

    /**
     * PG-009
     * Show data penggajian berhasil sebagai pemilik data.
     */
    public function test_show_penggajian_success_as_owner()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        $penggajian = $this->createPenggajian(['karyawan_id' => $karyawan->id]);

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(200);
    }

    /**
     * PG-010
     * Show gagal ketika karyawan bukan pemilik data.
     */
    public function test_show_penggajian_fails_as_non_owner()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        $otherKaryawan = $this->createKaryawan();
        $penggajian = $this->createPenggajian(['karyawan_id' => $otherKaryawan->id]);

        $response = $this->get("/penggajian/{$penggajian->id}");
        $response->assertStatus(403);
    }

    /**
     * PG-011
     * Show gagal ketika ID penggajian tidak ditemukan.
     */
    public function test_show_non_existing_penggajian()
    {
        $this->loginAsAdmin();
        $response = $this->get('/penggajian/99999');
        $response->assertStatus(404);
    }

    /**
     * PG-012
     * Update data penggajian berhasil.
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
        ]);
    }

    /**
     * PG-013
     * Update gagal ketika ID penggajian tidak ditemukan.
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
     * PG-014
     * Delete data penggajian berhasil.
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
     * PG-015
     * Delete gagal ketika ID penggajian tidak ditemukan.
     */
    public function test_destroy_non_existing_penggajian()
    {
        $this->loginAsAdmin();
        $response = $this->delete('/penggajian/99999');
        $response->assertStatus(404);
    }
}
