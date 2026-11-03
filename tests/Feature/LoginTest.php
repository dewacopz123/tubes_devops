<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY' . str_pad((string)(Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Budi Santoso',
            'email' => 'budi' . (Karyawan::count() + 1) . '@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    /**
     * WBT-01
     * login email kosong
     */
    public function test_login_fails_when_email_not_fill()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * WBT-02
     * login password kosong
     */
    public function test_login_fails_when_password_not_fill()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'budi@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * WBT-03
     * Login gagal email tidak di temukan.
     */
    public function test_login_fails_when_email_not_found()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'tidakditemukan@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * WBT-04
     * Login gagal ketika akun berstatus Nonaktif.
     */
    public function test_login_fails_when_account_inactive()
    {
        $this->createKaryawan([
            'email' => 'nonaktif@example.com',
            'password' => Hash::make('password123'),
            'status' => 'Nonaktif',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'nonaktif@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * WBT-05
     * Login gagal ketika password salah.
     */
    public function test_login_fails_when_password_incorrect()
    {
        $this->createKaryawan(['email' => 'budi@example.com']);

        $response = $this->from('/login')->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'salahpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * WBT-06
     * Login berhasil dengan kredensial yang valid.
     */
    public function test_login_success()
    {
        $karyawan = $this->createKaryawan([
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($karyawan);
    }

    /**
     * WBT-01-FR02 
     * Register gagal data tidak valid.
     */
    public function test_register_fails_when_data_invalid()
    {
        $response = $this->from('/login')->post('/register', [
            'nama' => 'invalid!@#',
            'email' => 'bukan-email',
            'password' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['nama', 'email']);
    }

    /**
     * wbt-02-FR02
     * Register berhasil dengan data yang valid.
     */
    public function test_register_success()
    {
        $response = $this->from('/login')->post('/register', [
            'nama' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'password' => 'pass123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('karyawans', ['email' => 'andi@example.com']);
    }
}