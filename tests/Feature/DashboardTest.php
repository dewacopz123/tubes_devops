<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    /**
     * TEST CASE 1 (POSITIF):
     * Halaman dashboard menampilkan metrik totalKaryawan, karyawanAktif, dan hadirHariIni.
     */
    public function test_dashboard_displays_realtime_metrics_total_aktif_dan_hadir_hari_ini(): void
    {
        $admin = $this->createKaryawan(['status' => 'Aktif']);
        $this->createKaryawan(['status' => 'Aktif']);
        $this->createKaryawan(['status' => 'Nonaktif']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalKaryawan', 3);
        $response->assertViewHas('karyawanAktif', 2);
        $response->assertViewHas('karyawanNonaktif', 1);
        $response->assertViewHas('hadirHariIni', 0);
    }

    /**
     * TEST CASE 2 (NEGATIF):
     * Akses dashboard tanpa otentikasi login diredirect ke /login.
     */
    public function test_dashboard_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * TEST CASE 3 (EDGE CASE):
     * Penghitungan hadirHariIni hanya menjumlahkan absensi dengan status Masuk/Selesai pada tanggal hari ini.
     * Mengabaikan tanggal kemarin atau status selain Masuk/Selesai (misal: Izin, Sakit, Alpha).
     */
    public function test_hadir_hari_ini_only_counts_attendance_with_status_masuk_or_selesai_today(): void
    {
        $k1 = $this->createKaryawan(['status' => 'Aktif']);
        $k2 = $this->createKaryawan(['status' => 'Aktif']);
        $k3 = $this->createKaryawan(['status' => 'Aktif']);
        $k4 = $this->createKaryawan(['status' => 'Aktif']);
        $k5 = $this->createKaryawan(['status' => 'Aktif']);

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // 1. Absensi kemarin dengan status Masuk (tidak boleh dihitung)
        Absensi::create([
            'karyawan_id' => $k1->id,
            'tanggal' => $yesterday,
            'jam_masuk' => '08:00:00',
            'status' => 'Masuk',
        ]);

        // 2. Absensi hari ini dengan status Izin (tidak boleh dihitung)
        Absensi::create([
            'karyawan_id' => $k2->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'status' => 'Izin',
        ]);

        // 3. Absensi hari ini dengan status Sakit (tidak boleh dihitung)
        Absensi::create([
            'karyawan_id' => $k3->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'status' => 'Sakit',
        ]);

        // 4. Absensi hari ini dengan status Masuk (HARUS dihitung)
        Absensi::create([
            'karyawan_id' => $k4->id,
            'tanggal' => $today,
            'jam_masuk' => '08:15:00',
            'status' => 'Masuk',
        ]);

        // 5. Absensi hari ini dengan status Selesai (HARUS dihitung)
        Absensi::create([
            'karyawan_id' => $k5->id,
            'tanggal' => $today,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($k1)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('hadirHariIni', 2);
    }

    /**
     * Memastikan rute alias admin dashboard dialihkan ke dashboard utama.
     */
    public function test_admin_dashboard_route_redirects_to_dashboard(): void
    {
        $admin = $this->createKaryawan(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Memastikan rute alias karyawan dashboard dialihkan ke dashboard utama.
     */
    public function test_karyawan_dashboard_route_redirects_to_dashboard(): void
    {
        $karyawan = $this->createKaryawan(['role' => 'karyawan']);
        $response = $this->actingAs($karyawan)->get('/karyawan/dashboard');

        $response->assertRedirect(route('dashboard'));
    }
}
