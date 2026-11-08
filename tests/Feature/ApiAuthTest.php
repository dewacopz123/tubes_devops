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

    /**
     * TEST CASE 1 (POSITIF):
     * Endpoint POST /api/login mengembalikan access_token valid (80 karakter) dan payload data user.
     */
    public function test_api_login_returns_valid_bearer_token_and_user_payload(): void
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
            'user' => ['id', 'kode_karyawan', 'nama', 'email', 'telepon', 'role', 'status'],
        ]);

        $data = $response->json();
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertEquals(80, strlen($data['access_token']));
        $this->assertEquals($karyawan->email, $data['user']['email']);
    }

    /**
     * TEST CASE 2 (NEGATIF):
     * Mengakses endpoint API privat tanpa header Authorization: Bearer <token>
     * ditolak dengan status 401 Unauthenticated.
     */
    public function test_accessing_private_api_endpoint_without_bearer_token_returns_unauthenticated(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated',
        ]);
    }

    /**
     * TEST CASE 3 (EDGE CASE):
     * Endpoint POST /api/logout berhasil menghapus (revoke) token dari database
     * sehingga token lama tidak bisa digunakan kembali untuk request berikutnya.
     */
    public function test_api_logout_revokes_token_and_prevents_subsequent_access(): void
    {
        $karyawan = $this->createKaryawan();
        $token = $karyawan->createToken();

        // 1. Panggil logout dengan token valid
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Berhasil logout',
        ]);

        // 2. Verifikasi token di database telah dicabut (null)
        $this->assertNull($karyawan->fresh()->api_token);

        // 3. Mengakses kembali endpoint yang sama dengan token lama harus ditolak (401)
        $subsequentResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $subsequentResponse->assertStatus(401);
        $subsequentResponse->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Menguji penolakan login jika password salah.
     */
    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $karyawan = $this->createKaryawan();

        $response = $this->postJson('/api/login', [
            'email' => $karyawan->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Email atau password salah',
        ]);
    }

    /**
     * Menguji penolakan login saat akun karyawan berstatus nonaktif.
     */
    public function test_api_login_fails_when_account_inactive(): void
    {
        $karyawan = $this->createKaryawan(['status' => 'Nonaktif']);

        $response = $this->postJson('/api/login', [
            'email' => $karyawan->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Menguji request dengan bearer token acak yang tidak terdaftar di database.
     */
    public function test_api_unauthorized_with_fake_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-12345')
            ->postJson('/api/logout');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }
}
