<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Etos Kerja - Profile</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="/css/menu_style.css?v=20260521">
    <link rel="stylesheet" href="/css/formAddEdit.css?v=20260521">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    {{-- NAVBAR --}}
    @include('Navbar.navbar')

    <div class="main-wrapper">
        <main class="profile">
            <section class="container">

                <div class="header">
                    <h2 class="title">Profile</h2>

                    <div class="profile-photo-wrapper">

                        <img src="{{ $karyawan->foto ?: asset('asset/Icon/profile.png') }}" alt="Foto Profil" class="photo" id="profilePhoto">

                        <label for="photoInput" style="display:none;">Unggah Foto Profil</label>
                        <input type="file" id="photoInput" accept=".jpg,.jpeg,.png" hidden aria-label="Unggah Foto Profil">

                        <button type="button" id="btnUploadPhoto" class="btn-primary btn-upload-photo"
                            style="display:none;">
                            <i class="fas fa-camera"></i>
                            Ganti Foto
                        </button>

                    </div>
                </div>

                {{-- FORM PROFILE --}}
                <form class="form" id="formProfile" method="POST" action="{{ route('profile.update') }}">
                    @csrf

                    <div class="group">
                        <label for="inputId" class="label">ID</label>
                        <input id="inputId" type="text" value="{{ $karyawan->id }}" disabled>
                    </div>

                    <div class="row">
                        <div class="group">
                            <label for="inputNama" class="label">Nama</label>
                            <input id="inputNama" type="text" name="nama" value="{{ old('nama', $karyawan->nama) }}"
                                required disabled>
                            <span class="input-error" id="error-nama"></span>
                        </div>

                        <div class="group">
                            <label for="inputEmail" class="label">Email</label>
                            <input id="inputEmail" type="email" name="email"
                                value="{{ old('email', $karyawan->email) }}" required disabled>
                            <span class="input-error" id="error-email"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="group">
                            <label for="inputTelepon" class="label">Telepon</label>
                            <input id="inputTelepon" type="text" name="telepon"
                                value="{{ old('telepon', $karyawan->telepon) }}" disabled>
                            <span class="input-error" id="error-telepon"></span>
                        </div>

                        <div class="group">
                            <label for="inputRole" class="label">Role</label>
                            <input id="inputRole" type="text" value="{{ $karyawan->role }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="group">
                            <label for="inputStatus" class="label">Status</label>
                            <input id="inputStatus" type="text" value="{{ $karyawan->status }}" disabled>
                        </div>
                        <div class="group"></div>
                    </div>

                    <div class="button-group">
                        <button type="button" id="btnEdit" class="btn-primary">Edit</button>
                        <button type="submit" id="btnSave" class="btn-jobdesk" style="display:none;">Simpan</button>
                        <button type="button" id="btnCancel" class="btn-danger" style="display:none;">Batal</button>
                    </div>

                </form>

            </section>
        </main>
    </div>

    {{-- Containers untuk dialog --}}
    <div id="popupContainer"></div>
    <div id="dialogContainer"></div>

    {{-- JS --}}
    <script src="/js/navbar.js?v=20260521"></script>
    <script src="/js/sek-notify.js?v=20260521"></script>
    <script src="/js/profile.js?v=20260521"></script>

</body>

</html>
