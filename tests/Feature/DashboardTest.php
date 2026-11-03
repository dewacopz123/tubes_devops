<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Test User',
            'email' => 'user'.(Karyawan::count() + 1).'@example.com',
            'telepon' => '08123456789',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Aktif',
        ], $attributes));
    }

    public function test_dashboard_can_be_accessed_by_authenticated_user()
    {
        $karyawan = $this->createKaryawan();
        $response = $this->actingAs($karyawan)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalKaryawan');
        $response->assertViewHas('karyawanAktif');
        $response->assertViewHas('hadirHariIni');
    }

    public function test_dashboard_redirects_unauthenticated_user()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_route_redirects_to_dashboard()
    {
        $admin = $this->createKaryawan(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_karyawan_dashboard_route_redirects_to_dashboard()
    {
        $karyawan = $this->createKaryawan(['role' => 'karyawan']);
        $response = $this->actingAs($karyawan)->get('/karyawan/dashboard');

        $response->assertRedirect(route('dashboard'));
    }
}

