<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileEditTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Ilham Pratama',
            'email' => 'ilham'.(Karyawan::count() + 1).'@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    /**
     * Test Case 1 (Positif):
     * Mengubah nama dan telepon dengan data valid berhasil tersimpan di database.
     */
    public function test_karyawan_can_update_profile_with_valid_data()
    {
        $karyawan = $this->createKaryawan([
            'nama' => 'Nama Awal',
            'email' => 'nama.awal@example.com',
            'telepon' => '081111111111',
        ]);

        $this->actingAs($karyawan);

        $response = $this->put('/profile', [
            'nama' => 'Nama Baru Valid',
            'email' => 'nama.awal@example.com',
            'telepon' => '082222222222',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profile berhasil diperbarui');

        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => 'Nama Baru Valid',
            'email' => 'nama.awal@example.com',
            'telepon' => '082222222222',
        ]);
    }

    /**
     * Test Case 2 (Negatif):
     * Mengosongkan field wajib (nama/email) menampilkan pesan error validasi.
     */
    public function test_karyawan_cannot_update_profile_with_empty_required_fields()
    {
        $karyawan = $this->createKaryawan([
            'nama' => 'Nama Tetap',
            'email' => 'tetap@example.com',
        ]);

        $this->actingAs($karyawan);

        $response = $this->from('/profile')->put('/profile', [
            'nama' => '',
            'email' => '',
            'telepon' => '081234567890',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHasErrors(['nama', 'email']);

        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'nama' => 'Nama Tetap',
            'email' => 'tetap@example.com',
        ]);
    }

    /**
     * Test Case 3 (Edge Case):
     * Mencoba mengubah email menggunakan email yang sudah terdaftar pada akun karyawan lain ditolak (unique constraint).
     */
    public function test_karyawan_cannot_update_profile_with_duplicate_email()
    {
        $userLain = $this->createKaryawan([
            'nama' => 'Karyawan Lain',
            'email' => 'terdaftar@example.com',
        ]);

        $karyawan = $this->createKaryawan([
            'nama' => 'Karyawan Uji',
            'email' => 'karyawan.uji@example.com',
        ]);

        $this->actingAs($karyawan);

        $response = $this->from('/profile')->put('/profile', [
            'nama' => 'Karyawan Uji Diubah',
            'email' => 'terdaftar@example.com',
            'telepon' => '089999999999',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHasErrors('email');

        // Pastikan email di database tidak berubah menjadi milik user lain
        $this->assertDatabaseHas('karyawans', [
            'id' => $karyawan->id,
            'email' => 'karyawan.uji@example.com',
        ]);
    }
}
