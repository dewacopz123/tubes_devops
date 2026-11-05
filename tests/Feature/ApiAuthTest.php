<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'API User',
            'email' => 'api'.(Karyawan::count() + 1).'@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('secret123'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    public function test_api_login_success()
    {
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/api/login', [
            'email' => $karyawan->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => ['id', 'nama', 'email'],
        ]);
    }

    public function test_api_login_fails_with_invalid_credentials()
    {
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/api/login', [
            'email' => $karyawan->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Email atau password salah']);
    }

    public function test_api_login_fails_when_account_inactive()
    {
        $karyawan = $this->createKaryawan(['status' => 'Nonaktif']);

        $response = $this->postJson('/api/login', [
            'email' => $karyawan->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_logout_success()
    {
        $karyawan = $this->createKaryawan();
        $token = $karyawan->createToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Berhasil logout']);
        $this->assertNull($karyawan->fresh()->api_token);
    }

    public function test_api_unauthenticated_without_token()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }

    public function test_api_unauthorized_with_fake_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-123')
            ->postJson('/api/logout');

        $response->assertStatus(401);
    }
}
