<?php

namespace Tests\Feature;

use App\Models\Jobdesk;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DataKaryawanTest extends TestCase
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
    }

    /*
    |--------------------------------------------------------------------------
    | Fitur 1: Tambah Karyawan Otomatis (Auto-Generate Code & Hash)
    |--------------------------------------------------------------------------
    */

    /**
     * KRY-013 (Positif)
     * Tambah karyawan tersimpan dengan status 201 dan kode karyawan otomatis terbentuk.
     */
    public function test_store_karyawan_generates_kode_karyawan_automatically()
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/karyawan', [
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'telepon' => '081234567890',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['success' => true]);

        $kode = $response->json('data.kode_karyawan');
        $this->assertMatchesRegularExpression('/^KRY-[A-Z0-9]{6}$/', $kode);
        $this->assertDatabaseHas('karyawans', [
            'email' => 'dewi@example.com',
            'kode_karyawan' => $kode,
        ]);
    }

    /**
     * KRY-014 (Negatif)
     * Input email dengan format tidak valid ditolak sistem.
     */
    public function test_store_karyawan_rejects_invalid_email()
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/karyawan', [
            'nama' => 'Dewi Lestari',
            'email' => 'dewi-bukan-email',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email' => 'Format email tidak valid.']);
        $this->assertDatabaseMissing('karyawans', ['nama' => 'Dewi Lestari']);
    }

    /**
     * KRY-015 (Edge Case)
     * Password default tersimpan dalam bentuk hash bcrypt, bukan plain text.
     */
    public function test_store_karyawan_hashes_default_password()
    {
        $this->loginAsAdmin();

        $this->postJson('/karyawan', [
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ])->assertStatus(201);

        $storedPassword = Karyawan::where('email', 'dewi@example.com')->value('password');

        $this->assertNotSame('123456', $storedPassword);
        $this->assertSame('bcrypt', password_get_info($storedPassword)['algoName']);
        $this->assertTrue(Hash::check('123456', $storedPassword));
    }

    /**
     * KRY-016
     * Karyawan biasa tidak boleh menambah data karyawan.
     */
    public function test_store_karyawan_forbidden_for_non_admin()
    {
        $this->actingAs($this->createKaryawan());

        $response = $this->postJson('/karyawan', [
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('karyawans', ['email' => 'dewi@example.com']);
    }

    /*
    |--------------------------------------------------------------------------
    | Fitur 2: Deaktivasi & Penguncian Akun Karyawan
    |--------------------------------------------------------------------------
    */

    /**
     * KRY-017 (Positif)
     * Status karyawan berhasil diubah dari Aktif menjadi Nonaktif.
     */
    public function test_update_status_karyawan_to_nonaktif()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan(['status' => 'Aktif']);

        $response = $this->putJson("/karyawan/{$karyawan->id}", [
            'nama' => $karyawan->nama,
            'email' => $karyawan->email,
            'role' => 'karyawan',
            'status' => 'Nonaktif',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'status' => 'Nonaktif',
        ]);
    }

    /**
     * KRY-018 (Negatif)
     * Karyawan Nonaktif ditolak saat login dengan pesan "Akun Anda tidak aktif".
     */
    public function test_nonaktif_karyawan_cannot_login()
    {
        $karyawan = $this->createKaryawan([
            'email' => 'resign@example.com',
            'password' => Hash::make('123456'),
            'status' => 'Nonaktif',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $karyawan->email,
            'password' => '123456',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Akun Anda tidak aktif']);
        $this->assertGuest();
    }

    /**
     * KRY-019 (Edge Case)
     * Karyawan Nonaktif tidak dapat mencatat absensi baru.
     */
    public function test_nonaktif_karyawan_cannot_record_absensi()
    {
        $karyawan = $this->createKaryawan(['status' => 'Nonaktif']);
        $this->actingAs($karyawan);

        $response = $this->postJson('/absensi/masuk');

        $response->assertStatus(403);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseMissing('absensis', ['karyawan_id' => $karyawan->id]);
    }

    /**
     * KRY-020
     * Sesi karyawan yang sedang login langsung terputus setelah dinonaktifkan.
     */
    public function test_session_of_deactivated_karyawan_is_terminated()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);
        $karyawan->update(['status' => 'Nonaktif']);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Akun Anda tidak aktif']);
        $this->assertGuest();
    }

    /**
     * KRY-021
     * Menonaktifkan karyawan ikut mencabut token API-nya.
     */
    public function test_deactivation_revokes_api_token()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();
        $karyawan->createToken();

        $this->putJson("/karyawan/{$karyawan->id}", [
            'nama' => $karyawan->nama,
            'email' => $karyawan->email,
            'role' => 'karyawan',
            'status' => 'Nonaktif',
        ])->assertStatus(200);

        $this->assertNull($karyawan->fresh()->api_token);
    }

    /**
     * KRY-022
     * Admin tidak dapat menonaktifkan akunnya sendiri.
     */
    public function test_admin_cannot_deactivate_own_account()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->putJson("/karyawan/{$admin->id}", [
            'nama' => $admin->nama,
            'email' => $admin->email,
            'role' => 'admin',
            'status' => 'Nonaktif',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('karyawans', ['id' => $admin->id, 'status' => 'Aktif']);
    }

    /*
    |--------------------------------------------------------------------------
    | Fitur 3: Penghapusan Karyawan dengan Proteksi Data Kosong
    |--------------------------------------------------------------------------
    */

    /**
     * KRY-023 (Positif)
     * Karyawan terdaftar berhasil dihapus dan record hilang dari database.
     */
    public function test_destroy_registered_karyawan_removes_record()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();

        $response = $this->deleteJson("/karyawan/{$karyawan->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('karyawans', ['id' => $karyawan->id]);
    }

    /**
     * KRY-024 (Negatif)
     * Menghapus ID karyawan fiktif menghasilkan respons 404 Not Found.
     */
    public function test_destroy_fictitious_karyawan_returns_not_found()
    {
        $this->loginAsAdmin();

        $response = $this->deleteJson('/karyawan/99999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Data karyawan tidak ditemukan.',
        ]);
    }

    /**
     * KRY-025 (Edge Case)
     * Menghapus karyawan yang memiliki jobdesk melepaskan relasi pivot dengan aman.
     */
    public function test_destroy_karyawan_detaches_jobdesk_pivot()
    {
        $this->loginAsAdmin();
        $karyawan = $this->createKaryawan();
        $jobdesk = Jobdesk::create([
            'kode_jobdesk' => 'JD-001',
            'nama_jobdesk' => 'Gudang',
            'tugas_utama' => 'Mengelola stok',
        ]);
        $karyawan->jobdesks()->attach($jobdesk->id);

        $response = $this->deleteJson("/karyawan/{$karyawan->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('jobdesk_karyawan', ['karyawan_id' => $karyawan->id]);
        $this->assertDatabaseHas('jobdesks', ['id' => $jobdesk->id]);
    }

    /**
     * KRY-026
     * Admin tidak dapat menghapus akunnya sendiri.
     */
    public function test_admin_cannot_delete_own_account()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->deleteJson("/karyawan/{$admin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('karyawans', ['id' => $admin->id]);
    }
}
