<?php

use App\Http\Controllers\{
    DashboardController,
    PengajuanKegiatanController,
    VerifikasiBauakController,
    PersetujuanWarek3Controller,
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
        Route::post('/{notifikasi}/destroy', [NotifikasiController::class, 'destroy'])->name('destroy');
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

    });

    // ==========================================
    // PENGAJUAN KEGIATAN ROUTES (ORMAWA + BAUAK)
    // ==========================================
    // Index & Show: accessible by ORMAWA and BAUAK (controller handles filtering)
    // Route::middleware(['auth', 'role:ormawa|bauak'])->prefix('pengajuan')->name('pengajuan.')->group(function () {
    //     Route::get('/', [PengajuanKegiatanController::class, 'index'])->name('index');
    //     Route::get('/{pengajuan}', [PengajuanKegiatanController::class, 'show'])->name('show');

    //     // Create, Edit, Update: ORMAWA only (with complete profile)
    //     Route::middleware('role:ormawa')->middleware('ormawa.complete')->group(function () {
    //         Route::get('/create', [PengajuanKegiatanController::class, 'create'])->name('create');
    //         Route::post('/', [PengajuanKegiatanController::class, 'store'])->name('store');
    //         Route::get('/{pengajuan}/edit', [PengajuanKegiatanController::class, 'edit'])->name('edit');
    //         Route::patch('/{pengajuan}', [PengajuanKegiatanController::class, 'update'])->name('update');
    //     });

    //     // Export & Print: accessible by both
    //     Route::get('/export/csv', [PengajuanKegiatanController::class, 'exportCSV'])->name('exportCSV');
    //     Route::get('/print/view', [PengajuanKegiatanController::class, 'printView'])->name('printView');
    // });

    Route::middleware(['auth', 'role:ormawa|bauak'])
    ->prefix('pengajuan')
    ->name('pengajuan.')
    ->group(function () {

        // INDEX
        Route::get('/', [PengajuanKegiatanController::class, 'index'])->name('index');

        // CREATE harus di atas route dinamis
        Route::middleware(['role:ormawa', 'ormawa.complete'])->group(function () {
            Route::get('/create', [PengajuanKegiatanController::class, 'create'])->name('create');
            Route::post('/', [PengajuanKegiatanController::class, 'store'])->name('store');
        });

        // ROUTE DINAMIS TARUH PALING BAWAH
        Route::get('/{pengajuan}', [PengajuanKegiatanController::class, 'show'])->name('show');

        Route::middleware(['role:ormawa', 'ormawa.complete'])->group(function () {
            Route::get('/{pengajuan}/edit', [PengajuanKegiatanController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [PengajuanKegiatanController::class, 'update'])->name('update');
        });

        // Export & Print
        Route::get('/export/csv', [PengajuanKegiatanController::class, 'exportCSV'])->name('exportCSV');
        Route::get('/print/view', [PengajuanKegiatanController::class, 'printView'])->name('printView');
    });


    // ==========================================


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

            

        // Untuk admin juga bisa akses semua resource
        Route::resource('pengajuan', PengajuanKegiatanController::class);
        Route::resource('ormawa', OrmawaController::class);
    });
});
