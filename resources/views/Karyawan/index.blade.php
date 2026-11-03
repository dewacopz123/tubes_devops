<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Etos Kerja - Data Karyawan</title>

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
                    <label>Nama</label>
                    <select id="filterNama" class="searchCategory">
                        <option value="">Semua</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>

                    <label>Role</label>
                    <select id="filterRole" class="searchCategory">
                        <option value="">Semua</option>
                        <option value="Karyawan">Karyawan</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>

            {{-- BUTTON --}}
            @if($isAdmin)
                <button id="btnAddKaryawan" class="btn btn-primary btn-long">
                    <i class="fas fa-plus"></i> Tambah Data Karyawan
                </button>
            @endif

            {{-- TABLE --}}
            <div class="card-content">
                <h3>Tabel Data Karyawan</h3>

                <div class="table-responsive">
                    <table class="table-absensi">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Role</th>
                                <th>Status</th>
                                @if($isAdmin)
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($karyawans as $k)
                                <tr data-nama="{{ strtolower($k->nama) }}" data-role="{{ strtolower($k->role) }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $k->nama }}</td>
                                    <td>{{ $k->email }}</td>
                                    <td>{{ $k->telepon ?? '-' }}</td>
                                    <td>{{ $k->role }}</td>
                                    <td>
                                        <span class="badge
                                        {{ $k->status === 'Aktif' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $k->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($isAdmin)
                                            <button type="button" class="badge badge-biru btnEdit" data-id="{{ $k->id }}"
                                                title="Edit">Edit</button>

                                            <button type="button" class="badge badge-danger btnDelete" data-id="{{ $k->id }}"
                                                title="Hapus">Hapus</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center">
                                        Data karyawan belum tersedia
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
    <script src="/js/data_karyawan.js?v=20260521"></script>

</body>

</html>
