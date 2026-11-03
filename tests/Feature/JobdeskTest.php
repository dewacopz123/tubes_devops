<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Jobdesk;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class JobdeskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * WBT-001
     * Menguji halaman index jobdesk.
     */
    private function login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
    }

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY' . str_pad((string) Karyawan::count() + 1, 3, '0', STR_PAD_LEFT),
            'nama' => 'Budi Santoso',
            'email' => 'budi' . (Karyawan::count() + 1) . '@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    private function createJobdesk(array $attributes = []): Jobdesk
    {
        $karyawan = $attributes['karyawan_id'] ?? $this->createKaryawan()->id;

        return Jobdesk::create(array_merge([
            'nama_jobdesk' => 'Admin Sistem',
            'tugas_utama' => 'Mengelola sistem perusahaan',
            'karyawan_id' => $karyawan,
        ], $attributes));
    }

    public function test_index_page_can_be_accessed()
    {
        $this->login();
        $response = $this->get('/jobdesk');

        $response->assertStatus(200);
    }

    /**
     * WBT-003
     * Store data berhasil.
     */
    public function test_store_jobdesk_success()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $response = $this->post('/jobdesk', [
            'nama_jobdesk' => 'Admin Sistem',
            'tugas_utama' => 'Mengelola sistem perusahaan',
            'karyawan_id' => $karyawan->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('jobdesks', [
            'nama_jobdesk' => 'Admin Sistem',
            'karyawan_id' => $karyawan->id,
        ]);
    }

    /**
     * WBT-003
     * Store data data invalid.
     */
    public function test_store_jobdesk_invalid()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $response = $this->post('/jobdesk', [
            'nama_jobdesk' => '',
            'tugas_utama' => 'Mengelola sistem perusahaan',
            'karyawan_id' => $karyawan->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('jobdesks', [
            'nama_jobdesk' => 'Admin Sistem',
            'karyawan_id' => $karyawan->id,
        ]);
    }

    /**
     * WBT-007
     * Update berhasil.
     */
    public function test_update_jobdesk_success()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $jobdesk = $this->createJobdesk([
            'karyawan_id' => $karyawan->id,
        ]);

        $response = $this->put("/jobdesk/{$jobdesk->id}", [
            'nama_jobdesk' => 'Supervisor',
            'tugas_utama' => 'Mengawasi tim',
            'karyawan_id' => $karyawan->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('jobdesks', [
            'id' => $jobdesk->id,
            'nama_jobdesk' => 'Supervisor',
        ]);
    }

    /**
     * WBT-008
     * Update ID tidak ditemukan.
     */
    public function test_update_non_existing_jobdesk()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $response = $this->put('/jobdesk/99999', [
            'nama_jobdesk' => 'Supervisor',
            'tugas_utama' => 'Mengawasi tim',
            'karyawan_id' => $karyawan->id,
        ]);

        $response->assertStatus(404);
    }

    /**
     * WBT-011
     * Show data berhasil sebagai admin.
     */
    public function test_show_jobdesk_success()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $jobdesk = $this->createJobdesk([
            'karyawan_id' => $karyawan->id,
        ]);

        $response = $this->get("/jobdesk/{$jobdesk->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nama_jobdesk' => 'Admin Sistem',
            'tugas_utama' => 'Mengelola sistem perusahaan',
            'karyawan_id' => $karyawan->id,
        ]);
    }

    /**
     * WBT-011
     * Show data berhasil sebagai karyawan.
     */
    public function test_show_jobdesk_success_as_karyawan()
    {
        $karyawan = $this->createKaryawan();

        $jobdesk = $this->createJobdesk([
            'karyawan_id' => $karyawan->id,
        ]);

        $response = $this->getJson("/jobdesk/{$jobdesk->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'nama_jobdesk' => 'Admin Sistem',
            'tugas_utama' => 'Mengelola sistem perusahaan',
            'karyawan_id' => $karyawan->id,
        ]);
    }

    /**
     * WBT-011
     * Show karyawan tidak terdaftar.
     */
    public function test_show_karyawan_not_found()
    {
        $this->login();

        $response = $this->get('/jobdesk/99999');

        $response->assertStatus(404);
    }

    /**
     * WBT-015
     * Menguji assign karyawan pada form tambah/edit.
     */
    public function test_assign_karyawan_on_form()
    {
        $this->login();
        $karyawan = $this->createKaryawan();

        $response = $this->get('/jobdesk/form');

        $response->assertStatus(200);
        $response->assertSee($karyawan->nama);
    }

    /**
     * WBT-015
     * Menguji assign jobdesk ke 2 karyawan.
     */
    public function test_assign_2karyawan_on_onejobdesk()
    {
        $this->login();

        $karyawan1 = $this->createKaryawan([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $karyawan2 = $this->createKaryawan([
            'nama' => 'Andi Saputra',
            'email' => 'andi@example.com',
        ]);

        $response = $this->get('/jobdesk/form');

        $response->assertStatus(200);

        $response->assertSee($karyawan1->nama);
        $response->assertSee($karyawan2->nama);
    }

    public function test_remove_karyawan_success()
    {
        $this->login();

        $jobdesk = $this->createJobdesk();
        $karyawan = $this->createKaryawan();

        $jobdesk->karyawans()->attach($karyawan->id);

        $response = $this->deleteJson(
            "/jobdesk/{$jobdesk->id}/karyawan/{$karyawan->id}"
        );

        $response->assertStatus(200);
    }

    public function test_remove_karyawan_jobdesk_not_found()
    {
        $this->login();

        $karyawan = $this->createKaryawan();

        $response = $this->deleteJson(
            "/jobdesk/99999/karyawan/{$karyawan->id}"
        );

        $response->assertStatus(404);
    }

    /**
     * WBT-013
     * Delete berhasil.
     */
    public function test_delete_jobdesk_success()
    {
        $this->login();
        $jobdesk = $this->createJobdesk();

        $response = $this->delete("/jobdesk/{$jobdesk->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('jobdesks', [
            'id' => $jobdesk->id,
        ]);
    }

    /**
     * WBT-014
     * Delete ID tidak ditemukan.
     */
    public function test_delete_non_existing_jobdesk()
    {
        $this->login();

        $response = $this->delete('/jobdesk/99999');

        $response->assertStatus(404);
    }
}
