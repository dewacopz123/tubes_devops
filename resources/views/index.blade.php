<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Etos Kerja - Dashboard</title>

    <link rel="stylesheet" href="/css/menu_style.css?v=20260521">
    <link rel="stylesheet" href="/css/dashboard.css?v=20260521">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    @include('Navbar.navbar')

    <div class="main-wrapper">
        <div class="dashboard-container">

            <h2>Dashboard Sistem Etos Kerja</h2>

        <!-- JAM REALTIME -->
        <div class="clock-box">
            <h3 id="clock">00:00:00</h3>
            <p id="date">-</p>
        </div>

        <!-- STAT GRID -->
        <div class="stat-grid">

            <div class="stat-card">
                <h4>Total Karyawan</h4>
                <p>{{ $totalKaryawan }}</p>
            </div>

            <div class="stat-card success">
                <h4>Karyawan Aktif</h4>
                <p>{{ $karyawanAktif }}</p>
            </div>

            <div class="stat-card danger">
                <h4>Karyawan Nonaktif</h4>
                <p>{{ $karyawanNonaktif }}</p>
            </div>

            <div class="stat-card">
                <h4>Hadir Hari Ini</h4>
                <p>{{ $hadirHariIni }}</p>
            </div>

        </div>

        <div class="stat-grid">
            <div class="stat-card warning">
                <h4>Izin</h4>
                <p>{{ $izinHariIni }}</p>
            </div>

            <div class="stat-card danger">
                <h4>Alpha</h4>
                <p>{{ $alphaHariIni }}</p>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-card success">
                <h4>Karyawan Aktif Bertugas</h4>
                <p>{{ $karyawanPunyaJobdesk }}</p>
            </div>

            <div class="stat-card warning">
                <h4>Belum Ada Jobdesk</h4>
                <p>{{ $karyawanTanpaJobdesk }}</p>
            </div>
        </div>

        <div class="stat-grid">

            <div class="stat-card">
                <h4>Grafik Absensi Hari Ini</h4>
                <canvas id="absensiChart"></canvas>
            </div>

            <div class="stat-card">
                <h4>Keaktifan Jobdesk Karyawan</h4>
                <canvas id="jobdeskChart"></canvas>
            </div>

        </div>

        </div>
    </div>
    <script>
        window.hadirHariIni = { $hadirHariIni };
        window.izinHariIni = { $izinHariIni };
        window.alphaHariIni = { $alphaHariIni };
        window.karyawanPunyaJobdesk = { $karyawanPunyaJobdesk };
        window.karyawanTanpaJobdesk = { $karyawanTanpaJobdesk };
    </script>

    <script src="/js/dashboard.js?v=20260521"></script>

</body>

</html>
