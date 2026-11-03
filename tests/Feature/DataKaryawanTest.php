<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class DataKaryawanTest extends TestCase
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

    /**
     * KRY-001
     * Menguji halaman index karyawan dapat diakses.
     */
    public function test_index_page_can_be_accessed()
    {
        $this->loginAsAdmin();
        $response = $this->get('/karyawan');
        $response->assertStatus(200);
    }

    /**
     * KRY-002
     * Menguji halaman form karyawan dapat diakses.
     */
    public function test_form_page_can_be_accessed()
    {
        $this->loginAsAdmin();
        $response = $this->get('/karyawan/form');
        $response->assertStatus(200);
    }

    /**
     * KRY-003
     * Show data karyawan berhasil.
     */
    public function test_show_karyawan_success()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->get("/karyawan/{$karyawan->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $karyawan->id]);
    }

    /**
     * KRY-005
     * Store data karyawan berhasil.
     */
    public function test_store_karyawan_success()
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/karyawan', [
            'nama' => 'Siti Rahayu',
            'email' => 'siti@example.com',
            'telepon' => '081234567890',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('karyawans', ['email' => 'siti@example.com']);
    }

    /**
     * KRY-010
     * Update data karyawan berhasil.
     */
    public function test_update_karyawan_success()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->putJson("/karyawan/{$karyawan->id}", [
            'nama' => 'Budi Updated',
            'email' => $karyawan->email,
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => 'Budi Updated',
        ]);
    }

    /**
     * KRY-012
     * Delete data karyawan berhasil.
     */
    public function test_destroy_karyawan_success()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->delete("/karyawan/{$karyawan->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseMissing('karyawans', ['id' => $karyawan->id]);
    }

    /**
     * KRY-004
     * Show gagal ketika ID karyawan tidak ditemukan.
     */
    public function test_show_karyawan_not_found()
    {
        $this->loginAsAdmin();
        $response = $this->get('/karyawan/99999');
        $response->assertStatus(404);
    }

    /**
     * KRY-010
     * Update data karyawn gagal id tidak di temukan.
     */
        public function test_update_karyawan_not_found()
    {
        $this->loginAsAdmin();

        $response = $this->putJson('/karyawan/99999', [
            'nama' => 'Budi Updated',
            'email' => 'budi@example.com',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false
        ]);
    }

    /**
     * KRY-012
     * Delete data karyawan gagal id tidak ditemukan.
     */
    public function test_destroy_karyawan_not_found()
    {
        $this->loginAsAdmin();

        $response = $this->delete('/karyawan/99999');

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false
        ]);
    }
}
