<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Etos Kerja - Absensi</title>

    <link rel="stylesheet" href="/css/menu_style.css?v=20260521">
    <link rel="stylesheet" href="/css/formAddEdit.css?v=20260521">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    @include('Navbar.navbar')

    @php
        $isKaryawan = auth()->check() && strtolower(trim((string) auth()->user()->role)) === 'karyawan';
    @endphp

    <div class="main-wrapper">
        <main class="page-content">

            {{-- KHUSUS KARYAWAN --}}
            @auth

                <div class="card-content">
                    <p>
                        <b>Nama:</b> {{ auth()->user()->nama }}
                    </p>

                    <p>
                        <b>Status:</b>
                        <span class="badge 
                    {{ auth()->user()->status === 'Aktif' ? 'badge-success' : 'badge-danger' }}">
                            {{ auth()->user()->status }}
                        </span>
                    </p>
                </div>

                @if($isKaryawan)
                    <div class="button-group-horizontal">
                        <button class="btn btn-primary btn-full" id="btnMasukKerja">
                            <i class="fas fa-sign-in-alt"></i> Masuk Kerja
                        </button>

                        <button class="btn btn-danger btn-full" id="btnKeluarKerja">
                            <i class="fas fa-sign-out-alt"></i> Selesai Kerja
                        </button>
                    </div>
                @endif
            @endauth

            {{-- ADMIN & KARYAWAN BISA MELIHAT --}}
            <div class="table-responsive">
                <table class="table-absensi">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensis as $a)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($a->karyawan)->nama ?? '-' }}</td>
                                <td>{{ optional($a->karyawan)->email ?? '-' }}</td>
                                <td>{{ $a->tanggal }}</td>
                                <td>{{ $a->jam_masuk ?? '-' }}</td>
                                <td>{{ $a->jam_keluar ?? '-' }}</td>
                                <td>
                                    @if($a->status === 'Masuk')
                                        <span class="badge badge-success">Masuk</span>
                                    @elseif($a->status === 'Keluar')
                                        <span class="badge badge-danger">Keluar</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $a->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </main>
    </div>
    <div id="popupContainer"></div>
    <div id="dialogContainer"></div>
    <script src="/js/navbar.js?v=20260521"></script>
    <script src="/js/sek-notify.js?v=20260521"></script>
    <script src="/js/absensi.js?v=20260521"></script>

</body>

</html>