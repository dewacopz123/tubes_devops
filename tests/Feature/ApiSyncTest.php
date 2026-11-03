<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiSyncTest extends TestCase
{
    use RefreshDatabase;

    private Karyawan $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Karyawan::create([
            'kode_karyawan' => 'KRY-ADM01',
            'nama' => 'Admin Test',
            'email' => 'admin.sync@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        $this->token = $this->admin->createToken();
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_api_get_karyawans()
    {
        $response = $this->withHeaders($this->authHeaders())->getJson('/api/karyawans');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'nama', 'email'],
        ]);
    }

    public function test_api_store_karyawan()
    {
        $response = $this->withHeaders($this->authHeaders())->postJson('/api/karyawans', [
            'nama' => 'Karyawan Baru',
            'email' => 'baru@example.com',
            'telepon' => '081234567891',
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('karyawans', ['email' => 'baru@example.com']);
    }

    public function test_api_show_karyawan()
    {
        $response = $this->withHeaders($this->authHeaders())->getJson("/api/karyawans/{$this->admin->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->admin->id);
    }

    public function test_api_update_karyawan()
    {
        $response = $this->withHeaders($this->authHeaders())->putJson("/api/karyawans/{$this->admin->id}", [
            'nama' => 'Admin Updated',
            'email' => $this->admin->email,
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('karyawans', ['nama' => 'Admin Updated']);
    }

    public function test_api_destroy_karyawan()
    {
        $target = Karyawan::create([
            'kode_karyawan' => 'KRY-DEL',
            'nama' => 'To Delete',
            'email' => 'del@example.com',
            'password' => Hash::make('pass'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        $response = $this->withHeaders($this->authHeaders())->deleteJson("/api/karyawans/{$target->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('karyawans', ['id' => $target->id]);
    }

    public function test_api_jobdesk_crud_and_assign()
    {
        // Store
        $storeResponse = $this->withHeaders($this->authHeaders())->postJson('/api/jobdesks', [
            'nama_jobdesk' => 'Backend Dev',
            'tugas_utama' => 'Membangun API',
        ]);
        $storeResponse->assertStatus(201);
        $jobdeskId = $storeResponse->json('data.id');

        // Show
        $showResponse = $this->withHeaders($this->authHeaders())->getJson("/api/jobdesks/{$jobdeskId}");
        $showResponse->assertStatus(200);

        // Assign
        $assignResponse = $this->withHeaders($this->authHeaders())->postJson('/api/jobdesks/assign', [
            'jobdesk_id' => $jobdeskId,
            'karyawan_id' => [$this->admin->id],
        ]);
        $assignResponse->assertStatus(200);

        // Remove
        $removeResponse = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/jobdesks/{$jobdeskId}/karyawans/{$this->admin->id}");
        $removeResponse->assertStatus(200);

        // Destroy
        $destroyResponse = $this->withHeaders($this->authHeaders())->deleteJson("/api/jobdesks/{$jobdeskId}");
        $destroyResponse->assertStatus(200);
    }

    public function test_api_absensi_crud()
    {
        $masukResponse = $this->withHeaders($this->authHeaders())->postJson('/api/absensi/masuk', [
            'karyawan_id' => $this->admin->id,
            'status' => 'Masuk',
        ]);
        $masukResponse->assertStatus(201);

        $listResponse = $this->withHeaders($this->authHeaders())->getJson('/api/absensis');
        $listResponse->assertStatus(200);

        $keluarResponse = $this->withHeaders($this->authHeaders())->postJson('/api/absensi/keluar', [
            'karyawan_id' => $this->admin->id,
        ]);
        $keluarResponse->assertStatus(200);
    }

    public function test_api_penggajian_crud()
    {
        $storeResponse = $this->withHeaders($this->authHeaders())->postJson('/api/penggajians', [
            'karyawan_id' => $this->admin->id,
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'gaji_pokok' => 5000000,
            'tunjangan' => 500000,
            'potongan' => 100000,
            'metode_pembayaran' => 'Transfer',
            'status' => 'Lunas',
        ]);
        $storeResponse->assertStatus(201);
        $penggajianId = $storeResponse->json('data.id');

        $showResponse = $this->withHeaders($this->authHeaders())->getJson("/api/penggajians/{$penggajianId}");
        $showResponse->assertStatus(200);

        $listResponse = $this->withHeaders($this->authHeaders())->getJson('/api/penggajians');
        $listResponse->assertStatus(200);
    }
}
