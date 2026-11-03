<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY' . str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Budi Santoso',
            'email' => 'budi' . (Karyawan::count() + 1) . '@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('password'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    private function login(): Karyawan
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);
        return $karyawan;
    }

    /**
     * TC-WBT-FR08-01
     * Menguji fungsi index() profile berhasil.
     */
    public function test_profile_index_success()
    {
        $karyawan = $this->login();

        $response = $this->get('/profile');

        $response->assertStatus(200);
        $response->assertViewHas('karyawan');

        $response->assertSee($karyawan->nama);
    }

    /**
     * TC-WBT-FR08-02
     * Menguji fungsi index() saat data user tidak ditemukan.
     */
    public function test_profile_index_user_not_found()
    {
        $user = Karyawan::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/profile');

        $response->assertStatus(404);
    }

    /**
     * TC-WBT-FR08-03
     * Menguji update profile dengan data valid.
     */
    public function test_update_profile_success()
    {
        $karyawan = $this->login();

        $response = $this->put('/profile', [
            'nama' => 'Budi Updated',
            'email' => 'budiupdated@example.com',
            'telepon' => '081234567890',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => 'Budi Updated',
            'email' => 'budiupdated@example.com',
            'telepon' => '081234567890',
        ]);
    }

    /**
     * TC-WBT-FR08-04
     * Menguji update profile dengan request JSON.
     */
    public function test_update_profile_json_success()
    {
        $karyawan = $this->login();

        $response = $this->putJson('/profile', [
            'nama' => 'Budi Updated',
            'email' => 'budiupdated@example.com',
            'telepon' => '081234567890',
        ]);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Profile berhasil diperbarui.'
        ]);
    }

    /**
     * TC-WBT-FR08-05
     * Menguji update profile dengan request form biasa.
     */
    public function test_update_profile_form_success()
    {
        $karyawan = $this->loginAsKaryawan();

        $response = $this->put('/profile', [
            'nama' => 'Budi Updated',
            'email' => 'budiupdated@example.com',
            'telepon' => '081234567890',
        ]);

        $response->assertRedirect();

        $response->assertSessionHas(
            'success',
            'Profile berhasil diperbarui'
        );
    }

    /**
     * TC-WBT-FR08-06
     * Menguji validasi gagal saat update profile.
     */
    public function test_update_profile_validation_failed()
    {
        $karyawan = $this->loginAsKaryawan();

        $oldName = $karyawan->nama;

        $response = $this->put('/profile', [
            'nama' => '',
            'email' => 'email-tidak-valid',
            'telepon' => '081234567890',
        ]);

        $response->assertSessionHasErrors([
            'nama',
            'email'
        ]);

        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => $oldName
        ]);
    }
}
