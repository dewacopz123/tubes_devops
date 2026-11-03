<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Etos Kerja - Data Penggajian</title>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS --}}
    <link rel="stylesheet" href="/css/menu_style.css?v=20260521">
    <link rel="stylesheet" href="/css/formAddEdit.css?v=20260521">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    {{-- NAVBAR --}}
    @include('Navbar.navbar')

    @php
        $isAdmin = auth()->check() && strtolower(trim((string) auth()->user()->role)) === 'admin';
    @endphp

    <div class="main-wrapper">
        <main class="page-content">

            {{-- FILTER --}}
            <div class="card-content">
                <div>
                    <label>Nama Karyawan</label>
                    <select id="filterNama" class="searchCategory">
                        <option value="">Semua</option>
                        @foreach($karyawans as $k)
                            <option value="{{ strtolower($k->nama) }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>

                    <label>Tanggal</label>
                    <input type="date" id="filterTanggal" class="searchCategory">
                </div>
            </div>

            {{-- BUTTON TAMBAH --}}
            @auth
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:4px;">
                    @if($isAdmin)
                        <button id="btnAddPenggajian" class="btn btn-primary btn-long">
                            <i class="fas fa-plus"></i> Tambah Data Penggajian
                        </button>
                    @endif
                    <button type="button" class="btn btn-export-pill" onclick="window.location.href='/export/penggajian'">
                        <i class="fas fa-file-excel"></i> Export Penggajian
                    </button>
                </div>
            @endauth


            {{-- TABLE --}}
            <div class="card-content">
                <h3>Tabel Data Penggajian</h3>

                <div class="table-responsive">
                    <table class="table-absensi">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Kode</th>
                                <th>Nama Karyawan</th>
                                <th>Tanggal</th>
                                <th>Gaji Pokok</th>
                                @if($isAdmin)
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penggajians as $pg)
                                <tr data-nama="{{ strtolower(optional($pg->karyawan)->nama ?? '') }}"
                                    data-tanggal="{{ $pg->tanggal }}">
                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $pg->kode_penggajian }}</td>
                                    <td>{{ optional($pg->karyawan)->nama ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pg->tanggal)->format('d-m-Y') }}</td>
                                    <td>Rp {{ number_format($pg->gaji_pokok, 0, ',', '.') }}</td>

                                    @if($isAdmin)
                                        <td>
                                            <button type="button" class="badge badge-biru btnEdit" data-id="{{ $pg->id }}"
                                                title="Edit">Edit</button>
                                            <button type="button" class="badge badge-danger btnDelete" data-id="{{ $pg->id }}"
                                                title="Hapus">Hapus</button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center">
                                        Data penggajian belum tersedia
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    {{-- JS --}}
    <div id="popupContainer"></div>
    <div id="dialogContainer"></div>
    <script src="/js/navbar.js?v=20260521"></script>
    <script src="/js/sek-notify.js?v=20260521"></script>
    <script src="/js/penggajian.js?v=20260521"></script>

</body>

</html>