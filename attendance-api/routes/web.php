<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes — Admin Dashboard
|--------------------------------------------------------------------------
*/

// ── Tamu (belum login) ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Sudah login ───────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect root ke dashboard
    Route::get('/', fn() => redirect()->route('admin.dashboard'));

    Route::prefix('admin')->name('admin.')->group(function () {

        // Ringkasan
        Route::get('/dashboard', [DashboardController::class, 'ringkasan'])->name('dashboard');

        // Monitoring
        Route::get('/monitoring',        [DashboardController::class, 'monitoring'])->name('monitoring');
        Route::get('/monitoring/export', [DashboardController::class, 'exportMonitoring'])->name('monitoring.export');

        // Izin & Cuti
        Route::get('/izin',              [DashboardController::class, 'izin'])->name('izin');
        Route::get('/izin/create',       fn() => view('admin.izin-create'))->name('izin.create');
        Route::put('/izin/{leaveRequest}/setujui', [DashboardController::class, 'setujuiIzin'])->name('izin.setujui');
        Route::put('/izin/{leaveRequest}/tolak',   [DashboardController::class, 'tolakIzin'])->name('izin.tolak');

        // Sesi Absensi
        Route::get('/sesi',                        [DashboardController::class, 'sesi'])->name('sesi');
        Route::get('/sesi/create',                 fn() => view('admin.sesi-create'))->name('sesi.create');
        Route::get('/sesi/bulk',                   fn() => view('admin.sesi-bulk'))->name('sesi.bulk');
        Route::get('/sesi/{sesi}/edit',            fn($sesi) => view('admin.sesi-edit', compact('sesi')))->name('sesi.edit');
        Route::delete('/sesi/{attendanceSession}', [DashboardController::class, 'hapusSesi'])->name('sesi.destroy');

        // Karyawan
        Route::get('/karyawan',             [DashboardController::class, 'karyawan'])->name('karyawan');
        Route::get('/karyawan/create',      fn() => view('admin.karyawan-create'))->name('karyawan.create');
        Route::get('/karyawan/{user}/edit', fn($user) => view('admin.karyawan-edit', compact('user')))->name('karyawan.edit');
        Route::delete('/karyawan/{user}',   [DashboardController::class, 'hapusKaryawan'])->name('karyawan.destroy');

        // Cabang
        Route::get('/cabang',               [DashboardController::class, 'cabang'])->name('cabang');
        Route::get('/cabang/create',        fn() => view('admin.cabang-create'))->name('cabang.create');
        Route::get('/cabang/{cabang}/edit', fn($cabang) => view('admin.cabang-edit', compact('cabang')))->name('cabang.edit');

        // Laporan
        Route::get('/laporan',        [DashboardController::class, 'laporan'])->name('laporan');
        Route::get('/laporan/export', [DashboardController::class, 'eksporLaporan'])->name('laporan.export');

        // Notifikasi
        Route::get('/notifikasi',             [DashboardController::class, 'notifikasi'])->name('notifikasi');
        Route::post('/notifikasi/baca-semua', [DashboardController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
        Route::post('/notifikasi/kirim',      [DashboardController::class, 'kirimPengumuman'])->name('notifikasi.kirim');
    });
});
