<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Etos Kerja - Jobdesk</title>
    
    {{-- CSS --}}
    <link rel="stylesheet" href="/css/menu_style.css?v=20260521">
    <link rel="stylesheet" href="/css/formAddEdit.css?v=20260521">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    @include('Navbar.navbar')

    @php
        $isAdmin = auth()->check() && strtolower(trim((string) auth()->user()->role)) === 'admin';
    @endphp

    <div id="main-wrapper" class="main-wrapper">

        <main class="page-content">

            <div class="card-content">
                <div>
                    <label for="searchJobdeskName" class="">Nama Jobdesk</label>
                    <select class="searchCategory" id="searchJobdeskName">
                        <option value="">Choose</option>
                    </select>
                    <label for="searchKaryawanName" class="">Nama Karyawan</label>
                    <select class="searchCategory" id="searchKaryawanName">
                        <option value="">Choose</option>
                    </select>
                </div>
            </div>
            @if($isAdmin)
                <button id="btnAddJobdesk" class="btn btn-primary btn-long">
                    <i class="fas fa-plus"></i> Tambah Jobdesk
                </button>
                <button id="btnAssignJobdesk" class="btn btn-jobdesk btn-long" style="margin-left: 10px;">
                    <i class="fas fa-user-plus"></i> Assign Jobdesk
                </button>
            @endif

            <div class="card-content">
                <h3>Jobdesk Table</h3>
                <div class="table-responsive">
                    <table class="table-absensi" id="jobdeskTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Jobdesk</th>
                                <th>Nama Jobdesk</th>
                                <th>Tugas Utama</th>
                                <th>Nama Karyawan</th>
                                @if($isAdmin)
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobdesks as $jobdesk)
                                <tr data-id="{{ $jobdesk->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $jobdesk->kode_jobdesk }}</td>
                                    <td class="col-jobdesk">{{ $jobdesk->nama_jobdesk }}</td>
                                    <td>{{ $jobdesk->tugas_utama }}</td>
                                    <td class="col-karyawan">{{ $jobdesk->karyawans->pluck('nama')->implode(', ') ?: '-' }}</td>
                                    @if($isAdmin)
                                        <td>
                                            <button type="button" class="badge badge-biru btnEdit" data-id="{{ $jobdesk->id }}"
                                                title="Edit">Edit</button>
                                            <button type="button" class="badge badge-danger btnDelete" data-id="{{ $jobdesk->id }}"
                                                title="Hapus">Hapus</button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 6 : 5 }}" style="text-align:center">
                                        Data jobdesk belum tersedia
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <div id="popupContainer"></div>
    <div id="dialogContainer"></div>
    <script src="/js/navbar.js?v=20260521"></script>
    <script src="/js/sek-notify.js?v=20260521"></script>
    <script src="/js/jobdesk.js?v=20260521"></script>

</body>

</html>