<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(array $attributes = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'kode_karyawan' => 'KRY'.str_pad((string) (Karyawan::count() + 1), 3, '0', STR_PAD_LEFT),
            'nama' => 'Test RBAC User',
            'email' => 'rbac'.(Karyawan::count() + 1).'@example.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ], $attributes));
    }

    /**
     * Test Case 1 (Positif):
     * User dengan role admin berhasil membuka dashboard admin dan rute manajemen karyawan.
     */
    public function test_admin_can_access_admin_dashboard_and_karyawan_management()
    {
        $admin = $this->createKaryawan([
            'role' => 'admin',
            'email' => 'admin.rbac@example.com',
        ]);

        $this->actingAs($admin);

        // Akses dashboard admin
        $responseDashboard = $this->get('/admin/dashboard');
        $responseDashboard->assertRedirect(route('dashboard'));

        // Akses halaman manajemen data karyawan
        $responseKaryawan = $this->get('/karyawan');
        $responseKaryawan->assertStatus(200);

        // Akses halaman form data karyawan
        $responseForm = $this->get('/karyawan/form');
        $responseForm->assertStatus(200);
    }

    /**
     * Test Case 2 (Negatif):
     * User dengan role karyawan mencoba membuka halaman admin langsung diblokir dengan respons 403 Unauthorized.
     */
    public function test_karyawan_forbidden_to_access_admin_routes()
    {
        $karyawan = $this->createKaryawan([
            'role' => 'karyawan',
            'email' => 'staff.biasa@example.com',
        ]);

        $this->actingAs($karyawan);

        // Karyawan biasa mencoba akses dashboard admin
        $responseAdminDashboard = $this->get('/admin/dashboard');
        $responseAdminDashboard->assertStatus(403);

        // Karyawan biasa mencoba akses halaman kelola karyawan
        $responseKaryawan = $this->get('/karyawan');
        $responseKaryawan->assertStatus(403);

        // Karyawan biasa mencoba akses form tambah karyawan
        $responseForm = $this->get('/karyawan/form');
        $responseForm->assertStatus(403);
    }

    /**
     * Test Case 3 (Edge Case):
     * Pengguna yang belum login sama sekali mencoba mengakses URL internal langsung diredirect ke halaman /login.
     */
    public function test_unauthenticated_guest_redirected_to_login()
    {
        // Tamu mencoba akses /admin/dashboard
        $responseAdmin = $this->get('/admin/dashboard');
        $responseAdmin->assertRedirect('/login');

        // Tamu mencoba akses /karyawan
        $responseKaryawan = $this->get('/karyawan');
        $responseKaryawan->assertRedirect('/login');

        // Tamu mencoba akses /dashboard
        $responseDashboard = $this->get('/dashboard');
        $responseDashboard->assertRedirect('/login');
    }
}
