<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class CloudinaryUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Ilham Karyawan',
            'email' => 'ilham.upload'.(Karyawan::count() + 1).'@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    /**
     * Test Case 1 (Positif):
     * Upload file gambar JPG/PNG valid berukuran <= 2MB berhasil menghasilkan URL foto profil baru.
     */
    public function test_upload_photo_with_valid_image_success_and_saves_cloudinary_url()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        $fakeImageUrl = 'https://res.cloudinary.com/dummy-cloud/image/upload/v1234567890/sek/profile/foto_ilham.jpg';

        $apiResponse = new ApiResponse([
            'secure_url' => $fakeImageUrl,
        ], []);

        $mockUploadApi = Mockery::mock(UploadApi::class);
        $mockUploadApi->shouldReceive('upload')
            ->once()
            ->withArgs(function ($path, $options) {
                return isset($options['folder']) && $options['folder'] === 'sek/profile';
            })
            ->andReturn($apiResponse);

        Cloudinary::shouldReceive('uploadApi')
            ->andReturn($mockUploadApi);

        $file = UploadedFile::fake()->image('avatar.png', 300, 300)->size(500); // 500 KB <= 2MB

        $response = $this->postJson('/profile/upload-photo', [
            'foto' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'foto_url' => $fakeImageUrl,
        ]);

        $karyawan->refresh();
        $this->assertEquals($fakeImageUrl, $karyawan->foto);
        $this->assertEquals($fakeImageUrl, $karyawan->foto_url);
    }

    /**
     * Test Case 2 (Negatif):
     * Upload file non-gambar (seperti dokumen PDF/Word) ditolak oleh validasi sistem.
     */
    public function test_upload_photo_fails_when_uploading_non_image_file()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        // File format PDF (bukan gambar)
        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 300, 'application/pdf');

        $response = $this->postJson('/profile/upload-photo', [
            'foto' => $invalidFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['foto']);

        // Pastikan foto karyawan tidak berubah
        $karyawan->refresh();
        $this->assertNull($karyawan->foto);
    }

    /**
     * Test Case 3 (Edge Case):
     * Upload gambar dengan ukuran melebihi batas 2048 KB (2MB) ditolak validasi ukuran file.
     */
    public function test_upload_photo_fails_when_file_exceeds_max_size()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        // Buat file gambar dengan ukuran 3000 KB (> 2048 KB)
        $oversizedImage = UploadedFile::fake()->image('large_avatar.png', 1000, 1000)->size(3000);

        $response = $this->postJson('/profile/upload-photo', [
            'foto' => $oversizedImage,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['foto']);

        $karyawan->refresh();
        $this->assertNull($karyawan->foto);
    }

    /**
     * Test Case 4:
     * Upload foto gagal ketika user belum login / unauthenticated (status 401).
     */
    public function test_upload_photo_fails_when_unauthenticated()
    {
        $file = UploadedFile::fake()->image('avatar.png', 300, 300)->size(500);

        $response = $this->postJson('/profile/upload-photo', [
            'foto' => $file,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test Case 5:
     * Upload foto menangani error saat Cloudinary melempar Exception (status 500).
     */
    public function test_upload_photo_returns_500_when_cloudinary_throws_exception()
    {
        $karyawan = $this->createKaryawan();
        $this->actingAs($karyawan);

        $mockUploadApi = Mockery::mock(UploadApi::class);
        $mockUploadApi->shouldReceive('upload')
            ->once()
            ->andThrow(new \Exception('Cloudinary upload failure'));

        Cloudinary::shouldReceive('uploadApi')
            ->andReturn($mockUploadApi);

        $file = UploadedFile::fake()->image('avatar.png', 300, 300)->size(500);

        $response = $this->postJson('/profile/upload-photo', [
            'foto' => $file,
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Cloudinary upload failure',
        ]);
    }

    /**
     * Test Case 6:
     * Memverifikasi aksesor foto_url di Model Karyawan menangani HTTPS URL, HTTP URL, file lokal, dan null.
     */
    public function test_karyawan_foto_url_accessor_handles_various_paths()
    {
        $karyawan = $this->createKaryawan(['foto' => null]);
        $this->assertNull($karyawan->foto_url);

        $karyawan->foto = 'https://res.cloudinary.com/dummy/image/upload/sample.png';
        $this->assertEquals('https://res.cloudinary.com/dummy/image/upload/sample.png', $karyawan->foto_url);

        $karyawan->foto = 'http://example.com/sample.png';
        $this->assertEquals('http://example.com/sample.png', $karyawan->foto_url);

        $karyawan->foto = 'foto_lokal.png';
        $this->assertStringContainsString('storage/foto_lokal.png', $karyawan->foto_url);
    }
}
