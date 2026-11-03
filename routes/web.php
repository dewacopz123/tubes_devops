<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\JobdeskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExportController;

Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/register', [LoginController::class, 'register'])->name('register.process');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/masuk', [AbsensiController::class, 'masuk']);
    Route::post('/absensi/keluar', [AbsensiController::class, 'keluar']);

    Route::get('/jobdesk', [JobdeskController::class, 'index']);
    Route::post('/jobdesk', [JobdeskController::class, 'store']);
    Route::get('/jobdesk/form', [JobdeskController::class, 'form']);
    Route::get('/jobdesk/assign/form', [JobdeskController::class, 'assignForm']);
    Route::post('/jobdesk/assign', [JobdeskController::class, 'assign']);
    Route::get('/jobdesk/{id}', [JobdeskController::class, 'show']);
    Route::put('/jobdesk/{id}', [JobdeskController::class, 'update']);
    Route::delete('/jobdesk/{id}', [JobdeskController::class, 'destroy']);
    Route::delete('/jobdesk/{jobdesk}/karyawan/{karyawan}', [JobdeskController::class, 'removeKaryawan']);

    Route::get('/penggajian', [PenggajianController::class, 'index']);
    Route::get('/penggajian/create', [PenggajianController::class, 'create']);
    Route::post('/penggajian', [PenggajianController::class, 'store']);
    Route::get('/penggajian/{id}', [PenggajianController::class, 'show']);
    Route::put('/penggajian/{id}', [PenggajianController::class, 'update']);
    Route::delete('/penggajian/{id}', [PenggajianController::class, 'destroy']);

    Route::get('/karyawan', [KaryawanController::class, 'index']);
    Route::get('/karyawan/form', [KaryawanController::class, 'form']);
    Route::get('/karyawan/{id}', [KaryawanController::class, 'show']);
    Route::post('/karyawan', [KaryawanController::class, 'store']);
    Route::put('/karyawan/{id}', [KaryawanController::class, 'update']);
    Route::delete('/karyawan/{id}', [KaryawanController::class, 'destroy']);

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');

    Route::get('/export/laporan', [ExportController::class, 'laporan']);
    Route::get('/export/penggajian', [ExportController::class, 'penggajian']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/karyawan/dashboard', function () {
        return redirect()->route('dashboard');
    });
});
