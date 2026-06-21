<?php

use App\Http\Controllers\{
    DashboardController,
    PengajuanKegiatanController,
    VerifikasiBauakController,
    PersetujuanWarek3Controller,
    FasilitasController,
    NotifikasiController,
    ProfileController,
    OrmawaController,
    LaporanController
};
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (handled by Laravel Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Dashboard - Different for each role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Notifications
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('index');
        Route::post('/{notifikasi}/read', [NotifikasiController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('read-all');
    });
    // Profile accessible WITHOUT middleware (for completing profile)
    Route::middleware(['role:ormawa'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
    // ==========================================
    // ORMAWA ROUTES
    // ==========================================
    // Other routes WITH middleware (require complete profile)
    Route::middleware(['role:ormawa', 'ormawa.complete'])->group(function () {

        // Pengajuan Kegiatan
        Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
            Route::get('/', [PengajuanKegiatanController::class, 'index'])->name('index');
            Route::get('/create', [PengajuanKegiatanController::class, 'create'])->name('create');
            Route::post('/', [PengajuanKegiatanController::class, 'store'])->name('store');
            Route::get('/{pengajuan}', [PengajuanKegiatanController::class, 'show'])->name('show');
            Route::get('/{pengajuan}/edit', [PengajuanKegiatanController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [PengajuanKegiatanController::class, 'update'])->name('update');
        });

        // Peminjaman Sarpras
        Route::prefix('sarpras/peminjaman')->name('sarpras.peminjaman.')->group(function () {
            Route::get('/', [PeminjamanSarprasController::class, 'index'])->name('index');
            Route::get('/create', [PeminjamanSarprasController::class, 'create'])->name('create');
            Route::post('/', [PeminjamanSarprasController::class, 'store'])->name('store');
            Route::get('/{peminjaman}', [PeminjamanSarprasController::class, 'show'])->name('show');
        });
    });

    // Allow Ormawa without complete profile to access profile page
    Route::middleware(['role:ormawa'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    });

    // ==========================================
    // BAUAK ROUTES
    // ==========================================
    Route::middleware(['role:bauak'])->prefix('bauak')->name('bauak.')->group(function () {

        // Verifikasi Pengajuan
        Route::prefix('verifikasi')->name('verifikasi.')->group(function () {
            Route::get('/', [VerifikasiBauakController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [VerifikasiBauakController::class, 'show'])->name('show');
            Route::post('/{pengajuan}/verify', [VerifikasiBauakController::class, 'verify'])->name('verify');
            Route::post('/bulk-verify', [VerifikasiBauakController::class, 'bulkVerify'])->name('bulk-verify');
        });

        // Reports
        Route::get('/laporan', [LaporanController::class, 'bauak'])->name('laporan');
    });

    // ==========================================
    // WAREK III ROUTES
    // ==========================================
    Route::middleware(['role:warek3'])->prefix('warek3')->name('warek3.')->group(function () {

        // Persetujuan
        Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
            Route::get('/', [PersetujuanWarek3Controller::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [PersetujuanWarek3Controller::class, 'show'])->name('show');
            Route::post('/{pengajuan}/approve', [PersetujuanWarek3Controller::class, 'approve'])->name('approve');
            Route::post('/{pengajuan}/reject', [PersetujuanWarek3Controller::class, 'reject'])->name('reject');
        });

        // Monitoring
        Route::get('/monitoring', [PersetujuanWarek3Controller::class, 'monitoring'])->name('monitoring');
    });

    // ==========================================
    // SARPRAS ROUTES
    // ==========================================
    Route::middleware(['role:sarpras'])->prefix('sarpras')->name('sarpras.')->group(function () {

        // Manajemen Fasilitas
        // Route::resource('fasilitas', FasilitasController::class);

        Route::prefix('fasilitas')->name('fasilitas.')->group(function () {
            Route::get('/', [FasilitasController::class, 'index'])->name('index');
            Route::get('/create', [FasilitasController::class, 'create'])->name('create');
            Route::get('/{show}', [FasilitasController::class, 'show'])->name('show');
            Route::get('/{fasilitas}', [FasilitasController::class, 'update'])->name('update');
            Route::get('/{destroy}', [FasilitasController::class, 'destroy'])->name('destroy');
        });

        // Peminjaman Management
        Route::prefix('peminjaman')->name('peminjaman.')->group(function () {
            Route::get('/', [PeminjamanSarprasController::class, 'index'])->name('index');
            Route::get('/{peminjaman}', [PeminjamanSarprasController::class, 'show'])->name('show');
            Route::post('/{peminjaman}/approve', [PeminjamanSarprasController::class, 'approve'])->name('approve');
            Route::post('/{peminjaman}/pickup', [PeminjamanSarprasController::class, 'pickup'])->name('pickup');
            Route::post('/{peminjaman}/return', [PeminjamanSarprasController::class, 'return'])->name('return');
        });

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'sarpras'])->name('laporan');
    });

    // ==========================================
    // ADMIN ROUTES
    // ==========================================

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard'); // admin.dashboard
        Route::get('/laporan', [LaporanController::class, 'admin'])->name('laporan');

        Route::prefix('ormawa')->name('ormawa.')->group(function () {
            Route::get('/', [OrmawaController::class, 'index'])->name('index');
            Route::get('/create', [OrmawaController::class, 'create'])->name('create');
            Route::post('/', [OrmawaController::class, 'store'])->name('store');
            Route::get('/{pengajuan}', [OrmawaController::class, 'show'])->name('show');
            Route::get('/{pengajuan}/edit', [OrmawaController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [OrmawaController::class, 'update'])->name('update');
        });
        // Route::resource('users', UserController::class);
        Route::resource('ormawa', OrmawaController::class);
        Route::resource('fasilitas', FasilitasController::class);
        Route::resource('pengajuan', PengajuanKegiatanController::class);
        Route::resource('peminjaman', PeminjamanSarprasController::class);
    });
});
