<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['api.token'])->group(function () {
    $karyawansIdUri = '/karyawans/{id}';
    $jobdesksIdUri = '/jobdesks/{id}';
    $absensisIdUri = '/absensis/{id}';
    $penggajiansIdUri = '/penggajians/{id}';

    Route::post(
        '/profile/upload-photo',
        [ProfileController::class, 'uploadPhoto']
    );

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Karyawan
    |--------------------------------------------------------------------------
    */
    Route::get('/karyawans', [SyncController::class, 'karyawans']);
    Route::get($karyawansIdUri, [SyncController::class, 'showKaryawan']);
    Route::post('/karyawans', [SyncController::class, 'storeKaryawan']);
    Route::put($karyawansIdUri, [SyncController::class, 'updateKaryawan']);
    Route::delete($karyawansIdUri, [SyncController::class, 'destroyKaryawan']);

    /*
    |--------------------------------------------------------------------------
    | Jobdesk
    |--------------------------------------------------------------------------
    */
    Route::get('/jobdesks', [SyncController::class, 'jobdesks']);
    Route::get($jobdesksIdUri, [SyncController::class, 'showJobdesk']);

    Route::post('/jobdesks', [SyncController::class, 'storeJobdesk']);
    Route::put($jobdesksIdUri, [SyncController::class, 'updateJobdesk']);
    Route::delete($jobdesksIdUri, [SyncController::class, 'destroyJobdesk']);

    // Assign jobdesk ke satu atau banyak karyawan
    Route::post('/jobdesks/assign', [SyncController::class, 'assignJobdesk']);

    // Hapus karyawan dari jobdesk
    Route::delete(
        '/jobdesks/{jobdeskId}/karyawans/{karyawanId}',
        [SyncController::class, 'removeJobdeskKaryawan']
    );

    /*
    |--------------------------------------------------------------------------
    | Alias Jobdesk (opsional untuk kompatibilitas lama)
    |--------------------------------------------------------------------------
    */
    Route::post('/jobdesk', [SyncController::class, 'storeJobdesk']);
    Route::put('/jobdesk/{id}', [SyncController::class, 'updateJobdesk']);
    Route::delete('/jobdesk/{id}', [SyncController::class, 'destroyJobdesk']);

    Route::post('/jobdesk/assign', [SyncController::class, 'assignJobdesk']);

    Route::delete(
        '/jobdesk/{jobdeskId}/karyawan/{karyawanId}',
        [SyncController::class, 'removeJobdeskKaryawan']
    );

    /*
    |--------------------------------------------------------------------------
    | Absensi
    |--------------------------------------------------------------------------
    */
    Route::get('/absensis', [SyncController::class, 'absensis']);
    Route::get($absensisIdUri, [SyncController::class, 'showAbsensi']);
    Route::post('/absensis', [SyncController::class, 'storeAbsensi']);
    Route::put($absensisIdUri, [SyncController::class, 'updateAbsensi']);
    Route::delete($absensisIdUri, [SyncController::class, 'destroyAbsensi']);

    Route::post('/absensi/masuk', [SyncController::class, 'absensiMasuk']);
    Route::post('/absensi/keluar', [SyncController::class, 'absensiKeluar']);

    /*
    |--------------------------------------------------------------------------
    | Penggajian
    |--------------------------------------------------------------------------
    */
    Route::get('/penggajians', [SyncController::class, 'penggajians']);
    Route::get($penggajiansIdUri, [SyncController::class, 'showPenggajian']);

    Route::post('/penggajians', [SyncController::class, 'storePenggajian']);
    Route::put($penggajiansIdUri, [SyncController::class, 'updatePenggajian']);
    Route::delete($penggajiansIdUri, [SyncController::class, 'destroyPenggajian']);

    /*
    |--------------------------------------------------------------------------
    | Alias Penggajian (opsional untuk kompatibilitas lama)
    |--------------------------------------------------------------------------
    */
    Route::post('/penggajian', [SyncController::class, 'storePenggajian']);
    Route::put('/penggajian/{id}', [SyncController::class, 'updatePenggajian']);
    Route::delete('/penggajian/{id}', [SyncController::class, 'destroyPenggajian']);

});
