<?php

use App\Http\Controllers\{
    DashboardController,
    PengajuanKegiatanController,
    VerifikasiBauakController,
    PersetujuanKaprodiController,
    PersetujuanDekanController,
    PersetujuanRektorController,
    PersetujuanPpController,
    PersetujuanWarek3Controller,
    NotifikasiController,
    ProfileController,
    OrmawaController,
    OrmawaAnggotaController,
    FakultasController,
    DekanController,
    MahasiswaController,
    AkademikController,
    ProgramStudiController,
    KaprodiController,
    MahasiswaDashboardController,
    LpjController,
    VerifikasiLpjController,
    TelegramConnectionController,
    LaporanController,
    Proposal\ProposalController,
    Dekan\OrmawaFakultasController
};
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::post('/telegram/webhook', [TelegramConnectionController::class, 'webhook'])
    ->name('telegram.webhook');

// Auth routes (handled by Laravel Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware(['auth', 'password.changed'])->group(function () {

    // Dashboard - Different for each role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/telegram/otp', [TelegramConnectionController::class, 'generate'])->name('telegram.generate');
        Route::delete('/telegram', [TelegramConnectionController::class, 'disconnect'])->name('telegram.disconnect');
    });

    // Notifications
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('index');
        Route::post('/{notifikasi}/destroy', [NotifikasiController::class, 'destroy'])->name('destroy');
        Route::post('/{notifikasi}/read', [NotifikasiController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotifikasiController::class, 'markAllAsRead'])->name('read-all');
    });



    // ==========================================
    // LAPORAN PERTANGGUNGJAWABAN
    Route::middleware('role:ormawa|mahasiswa|bauak|kaprodi|dekan|warek3|rektor|pp|admin')
        ->prefix('lpj')->name('lpj.')->group(function () {
            Route::get('/', [LpjController::class, 'index'])->name('index');
            Route::middleware(['role:ormawa|mahasiswa', 'active.ormawa'])->group(function () {
                Route::get('/kegiatan/{pengajuan}/create', [LpjController::class, 'create'])->name('create');
                Route::post('/kegiatan/{pengajuan}', [LpjController::class, 'store'])->name('store');
                Route::get('/{lpj}/edit', [LpjController::class, 'edit'])->name('edit');
                Route::patch('/{lpj}', [LpjController::class, 'update'])->name('update');
                Route::delete('/{lpj}/lampiran/{lampiran}', [LpjController::class, 'destroyAttachment'])->name('lampiran.destroy');
            });
            Route::get('/{lpj}', [LpjController::class, 'show'])->name('show');
        });

    // ==========================================
    // PROPOSAL KEGIATAN MODULE ROUTES
    Route::middleware(['role:ormawa'])->group(function () {
        Route::resource('proposal-kegiatan', ProposalController::class)
            ->parameters(['proposal-kegiatan' => 'proposal']);
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

    Route::middleware(['auth', 'role:ormawa|bauak|kaprodi|dekan|warek3|rektor|admin|pp|mahasiswa'])
    ->prefix('pengajuan')
    ->name('pengajuan.')
    ->group(function () {

        // INDEX
        Route::get('/', [PengajuanKegiatanController::class, 'index'])->name('index');

        // CREATE harus di atas route dinamis
        Route::middleware(['role:ormawa|mahasiswa', 'ormawa.complete', 'active.ormawa'])->group(function () {
            Route::get('/create', [PengajuanKegiatanController::class, 'create'])->name('create');
            Route::post('/', [PengajuanKegiatanController::class, 'store'])->name('store');
        });

        // ROUTE DINAMIS TARUH PALING BAWAH
        Route::get('/{pengajuan}', [PengajuanKegiatanController::class, 'show'])->name('show');

        Route::middleware(['role:ormawa|mahasiswa', 'ormawa.complete', 'active.ormawa'])->group(function () {
            Route::get('/{pengajuan}/edit', [PengajuanKegiatanController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [PengajuanKegiatanController::class, 'update'])->name('update');
        });

        // Export & Print
        Route::get('/export/csv', [PengajuanKegiatanController::class, 'exportCSV'])->name('exportCSV');
        Route::get('/print/view', [PengajuanKegiatanController::class, 'printView'])->name('printView');
    });


    // ==========================================



    // ==========================================
    // KAPRODI ROUTES
    Route::middleware(['role:kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/ormawa', [PersetujuanKaprodiController::class, 'ormawaIndex'])->name('ormawa.index');
        Route::get('/ormawa/{ormawa}', [PersetujuanKaprodiController::class, 'ormawaShow'])->name('ormawa.show');
        Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
            Route::get('/', [PersetujuanKaprodiController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [PersetujuanKaprodiController::class, 'show'])->name('show');
            Route::post('/{pengajuan}', [PersetujuanKaprodiController::class, 'decide'])->name('decide');
        });
    });

    // ==========================================
    // DEKAN ROUTES
    Route::middleware(['role:dekan'])->prefix('dekan')->name('dekan.')->group(function () {
        Route::prefix('ormawa-fakultas')->name('ormawa.')->group(function () {
            Route::get('/', [OrmawaFakultasController::class, 'index'])->name('index');
            Route::get('/{ormawa}', [OrmawaFakultasController::class, 'show'])->name('show');
        });

        Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
            Route::get('/', [PersetujuanDekanController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [PersetujuanDekanController::class, 'show'])->name('show');
            Route::post('/{pengajuan}/approve', [PersetujuanDekanController::class, 'approve'])->name('approve');
            Route::post('/{pengajuan}/reject', [PersetujuanDekanController::class, 'reject'])->name('reject');
        });
    });

    // ==========================================
    // REKTOR ROUTES
    Route::middleware(['role:rektor'])->prefix('rektor')->name('rektor.')->group(function () {
        Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
            Route::get('/', [PersetujuanRektorController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [PersetujuanRektorController::class, 'show'])->name('show');
            Route::post('/{pengajuan}/approve', [PersetujuanRektorController::class, 'approve'])->name('approve');
            Route::post('/{pengajuan}/reject', [PersetujuanRektorController::class, 'reject'])->name('reject');
        });
    });

    Route::middleware(['role:pp'])->prefix('pp')->name('pp.')->group(function () {
        Route::prefix('persetujuan')->name('persetujuan.')->group(function () {
            Route::get('/', [PersetujuanPpController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [PersetujuanPpController::class, 'show'])->name('show');
            Route::post('/{pengajuan}/approve', [PersetujuanPpController::class, 'approve'])->name('approve');
            Route::post('/{pengajuan}/reject', [PersetujuanPpController::class, 'reject'])->name('reject');
        });
    });

    // ==========================================
    // BAUAK ROUTES
    // ==========================================
    Route::middleware(['role:bauak'])->prefix('bauak')->name('bauak.')->group(function () {

        Route::prefix('lpj')->name('lpj.')->group(function () {
            Route::get('/', [VerifikasiLpjController::class, 'index'])->name('index');
            Route::post('/{lpj}/keputusan', [VerifikasiLpjController::class, 'decide'])->name('decide');
        });

        // Verifikasi Pengajuan
        Route::prefix('verifikasi')->name('verifikasi.')->group(function () {
            Route::get('/', [VerifikasiBauakController::class, 'index'])->name('index');
            Route::get('/{pengajuan}', [VerifikasiBauakController::class, 'show'])->name('show');
            Route::post('/{pengajuan}/verify', [VerifikasiBauakController::class, 'verify'])->name('verify');
            Route::post('/bulk-verify', [VerifikasiBauakController::class, 'bulkVerify'])->name('bulk-verify');
        });

        // Kelola Ormawa
        Route::prefix('ormawa')->name('ormawa.')->group(function () {
            // Search route HARUS sebelum route dinamis
            Route::get('/search/mahasiswa', [OrmawaController::class, 'searchMahasiswa'])->name('search-mahasiswa');

            // Standard routes
            Route::get('/', [OrmawaController::class, 'index'])->name('index');
            Route::get('/create', [OrmawaController::class, 'create'])->name('create');
            Route::post('/', [OrmawaController::class, 'store'])->name('store');

            // Dynamic routes harus di akhir
            Route::get('/{pengajuan}', [OrmawaController::class, 'show'])->name('show');
            Route::get('/{pengajuan}/edit', [OrmawaController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [OrmawaController::class, 'update'])->name('update');
            Route::delete('/{pengajuan}', [OrmawaController::class, 'destroy'])->name('destroy');

            // Anggota resource
            Route::resource('{ormawa}/anggota', OrmawaAnggotaController::class)
                ->parameters(['anggota' => 'user'])
                ->except(['show']);

            Route::get('{ormawa}/anggota/search', [OrmawaAnggotaController::class, 'search'])
                ->name('anggota.search');
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
    // MAHASISWA ROUTES
    // ==========================================
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
        Route::post('/set-active-ormawa', [MahasiswaDashboardController::class, 'setActiveOrmawa'])
            ->name('setActiveOrmawa');
    });

    // ==========================================
    // ORMAWA MEMBER MANAGEMENT ROUTES
    // ==========================================
    Route::resource('ormawa.anggota', OrmawaAnggotaController::class)
        ->parameters(['anggota' => 'user'])
        ->except(['show']);

    Route::get('ormawa/{ormawa}/anggota/search', [OrmawaAnggotaController::class, 'search'])
        ->name('ormawa.anggota.search');

    // ==========================================
    // ADMIN ROUTES
    // ==========================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard'); // admin.dashboard
        Route::get('/laporan', [LaporanController::class, 'admin'])->name('laporan');

        Route::prefix('ormawa')->name('ormawa.')->group(function () {
            Route::get('/search/mahasiswa', [OrmawaController::class, 'searchMahasiswa'])->name('search-mahasiswa');
            Route::get('/', [OrmawaController::class, 'index'])->name('index');
            Route::get('/create', [OrmawaController::class, 'create'])->name('create');
            Route::post('/', [OrmawaController::class, 'store'])->name('store');
            Route::get('/{pengajuan}', [OrmawaController::class, 'show'])->name('show');
            Route::get('/{pengajuan}/edit', [OrmawaController::class, 'edit'])->name('edit');
            Route::patch('/{pengajuan}', [OrmawaController::class, 'update'])->name('update');
            Route::delete('/{pengajuan}', [OrmawaController::class, 'destroy'])->name('destroy');

            Route::resource('{ormawa}/anggota', OrmawaAnggotaController::class)
                ->parameters(['anggota' => 'user'])
                ->except(['show']);

            Route::get('{ormawa}/anggota/search', [OrmawaAnggotaController::class, 'search'])
                ->name('anggota.search');
        });

        Route::resource('fakultas', FakultasController::class)->except(['show']);
        Route::resource('dekan', DekanController::class)->except(['show']);
        Route::get('akademik', [AkademikController::class, 'index'])->name('akademik.index');
        Route::resource('prodi', ProgramStudiController::class)->except(['show']);
        Route::resource('kaprodi', KaprodiController::class)->except(['show']);
        Route::get('mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::post('mahasiswa/{mahasiswa}/reset-password', [MahasiswaController::class, 'resetPassword'])
            ->name('mahasiswa.reset-password');

        // Untuk admin juga bisa akses semua resource
        Route::resource('pengajuan', PengajuanKegiatanController::class);
    });
});
