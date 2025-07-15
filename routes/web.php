<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\dosenController;
use App\Http\Controllers\KepalaSubController;
use App\Http\Controllers\mahasiswaController;
use App\Http\Controllers\PengusulController;
use App\Http\Controllers\staffumumController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\tatausahaController;
use App\Http\Controllers\testController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotifikasiController;


Route::get('/', function () {
    return view('/auth/login');
})->name('welcome');

Route::get('/login', function () {
    return view('auth.login');
})->name('login.form')->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/reset-password',[AuthController::class,'lostpassword'])->name('lostpassword');
Route::get('/reset-password',[AuthController::class,'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
Route::post('/surat/store', [SuratController::class, 'store'])->name('surat.store');

Route::get('/access-revoked', function () {
    return response()->view('auth.access-revoked');
})->name('access.revoked');
Route::get('/access-restored', [AccessController::class, 'accessRestored'])->name('access.restored');
Route::get('/check-access', [AccessController::class, 'checkAccess'])->name('check.access');

// Logout
Route::get('/logout', function () {
    // Logout semua guard
    Auth::guard('pengusul')->logout();
    Auth::guard('staff')->logout();
    Auth::logout(); // default guard (jaga-jaga)

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login')->with('success', 'Berhasil logout!');
})->name('logout');

Route::get('/anggota/search', [PengusulController::class, 'searchAnggota']);
Route::get('/search-dosen', [dosenController::class, 'searchDosen'])->name('search.dosen');
Route::delete('/surat/{id}',[SuratController::class, 'destroy'])->name('surat.destroy');
Route::get('/pengajuan/search', [SuratController::class, 'pengajuansearch'])->name('pengajuan.search');
Route::get('/pengaturan',[PengusulController::class,'pengaturan'])->name('settings');
Route::post('/ubah-password', [AuthController::class, 'profileUpdatePassword'])->name('profile.update');
Route::post('/notifikasi/mark-read/{id}', [NotifikasiController::class, 'markRead'])->name('notifikasi.markRead');

Route::middleware(['multi-auth', 'check-privileges'])->group(function () {
    // ======================= Pengusul =======================
    Route::middleware('role:pengusul')->group(function () {
        // Mahasiswa
        Route::prefix('mahasiswa')->controller(mahasiswaController::class)->group(function () {
            Route::get('/', 'index')->name('mahasiswa.dashboard');
            Route::get('/index/data','data')->name('surat.data');
            Route::get('/pengajuan', 'pengajuan')->name('mahasiswa.pengajuansurat');
            Route::get('/search', 'search')->name('mahasiswa.search');
            Route::post('/statistics', 'getFilteredStatistics')->name('mahasiswa.statistics');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('mahasiswa.surat.edit');
            Route::put('/surat/{id}', [SuratController::class, 'update'])->name('mahasiswa.surat.update');
            Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('mahasiswa.surat.destroy');
            Route::get('/draft/data','draftData')->name('mahasiswa.draftData');
            Route::get('/draft', 'draft')->name('mahasiswa.draft');
            Route::get('/status', 'status')->name('mahasiswa.statussurat');
            Route::get('/status/data', 'getStatusSuratData')->name('mahasiswa.statussurat.data');
            Route::get('/setting', 'setting')->name('mahasiswa.setting');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('mahasiswa.statussurat.show');
            Route::get('/surat/{id}/download', 'downloadPdf')->name('mahasiswa.surat.downloadPdf');
        });

        // Dosen
        Route::prefix('dosen')->controller(dosenController::class)->group(function () {
            Route::get('/', 'index')->name('dosen.dashboard');
            Route::get('/index/data','data')->name('surat.data');
            Route::get('/pengajuan', 'pengajuan')->name('dosen.pengajuansurat');
            Route::get('/search', 'search')->name('dosen.search');
            Route::post('/statistics', 'getFilteredStatistics')->name('dosen.statistics');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('dosen.surat.edit');
            Route::put('/surat/{id}', [SuratController::class, 'update'])->name('dosen.surat.update');
            Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('dosen.surat.destroy');
            Route::get('/draft/data','draftData')->name('dosen.draftData');
            Route::get('/draft', 'draft')->name('dosen.draft');
            Route::get('/status', 'status')->name('dosen.statussurat');
            Route::get('/status/data', 'getStatusSuratData')->name('dosen.statussurat.data');
            Route::get('/setting', 'setting')->name('dosen.setting');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('dosen.statussurat.show');
            Route::get('/surat/{id}/download', 'downloadPdf')->name('dosen.surat.downloadPdf');
        });
    });

    // ======================= Staff =======================
    Route::middleware('role:staff')->group(function () {
        Route::prefix('staff-umum')->controller(staffumumController::class)->group(function () {
            Route::get('/', 'index')->name('staffumum.dashboard');
            Route::get('/search', 'search')->name('staffumum.search');
            Route::get('/statistik','statistik')->name('staffumum.statistik');
            Route::get('/terbitkan','terbitkan')->name('staffumum.terbitkan');
            Route::get('/statussurat','statussurat')->name('staffumum.statussurat');
            Route::get('/statussurat/data', 'getStatusSuratData')->name('staffumum.statussurat.data');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('staffumum.statussurat.show');
            Route::get('/jenissurat','jenissurat')->name('staffumum.jenissurat');
            Route::post('/jenis-surat', 'storeJenisSurat')->name('staffumum.jenissurat.store');
            Route::put('/jenis-surat/{id}', 'updateJenisSurat')->name('staffumum.jenissurat.update');
            Route::delete('/jenis-surat/{id}', 'destroyJenisSurat')->name('staffumum.jenissurat.destroy');
            Route::get('/tinjau-surat', 'tinjauSurat')->name('staffumum.tinjausurat');
            Route::get('/tinjau-surat/data', 'getSuratData')->name('staffumum.tinjau.data');
            Route::get('/surat/{id}/tinjau', 'showDetailSurat')->name('staffumum.tinjau.detail');
            Route::post('/surat/{id}/tolak', 'tolakSurat')->name('staffumum.surat.tolak');
            Route::post('/surat/{id}/approve', 'approveSurat')->name('staffumum.surat.approve');
            Route::get('/terbitkan/data', 'getTerbitkanData')->name('staffumum.terbitkan.data');
            Route::get('/terbitkan/{id}/detail', 'terbitkanDetail')->name('staffumum.terbitkan.detail');
            Route::post('/terbitkan/{id}/terbitkan', 'terbitkanSurat')->name('staffumum.terbitkan.surat');
            Route::post('/terbitkan/{id}/tolak', 'tolakSurat')->name('staffumum.terbitkan.tolak');
        });

        Route::prefix('tata-usaha')->controller(tatausahaController::class)->group(function () {
            Route::get('/', 'index')->name('tatausaha.dashboard');
            Route::get('/search', 'search')->name('tatausaha.search');
            Route::get('/statistik','statistik')->name('tatausaha.statistik');
            Route::get('/terbitkan','terbitkan')->name('tatausaha.terbitkan');
            Route::get('/terbitkan/data', 'getTerbitkanData')->name('tatausaha.terbitkan.data');
            Route::get('/terbitkan/{id}/detail', 'detail')->name('tatausaha.terbitkan.detail');
            Route::get('/statussurat','statussurat')->name('tatausaha.statussurat');
            Route::get('/statussurat/data', 'getStatusSuratData')->name('tatausaha.statussurat.data');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('tatausaha.statussurat.show');
            Route::get('/jenissurat','jenissurat')->name('tatausaha.jenissurat');
            Route::post('/jenis-surat', 'storeJenisSurat')->name('tatausaha.jenissurat.store');
            Route::put('/jenis-surat/{id}', 'updateJenisSurat')->name('tatausaha.jenissurat.update');
            Route::delete('/jenis-surat/{id}', 'destroyJenisSurat')->name('tatausaha.jenissurat.destroy');
            Route::get('/tinjau-surat','tinjauSurat')->name('tatausaha.tinjausurat');
            Route::get('/tinjau-surat/data', 'getSuratData')->name('tatausaha.tinjau.data');
            Route::get('/surat/{id}/tinjau', 'showDetailSurat')->name('tatausaha.tinjau.detail');
            Route::post('/surat/{id}/tolak', 'tolakSurat')->name('tatausaha.surat.tolak');
            Route::post('/surat/{id}/approve', 'approveSurat')->name('tatausaha.surat.approve');
            Route::post('/surat/{id}/terbitkan', 'terbitkanSurat')->name('tatausaha.surat.terbitkan');
        });
    });

    // ======================= Admin =======================
    Route::middleware('role:admin')->prefix('admin')->controller(adminController::class)->name('admin.')->group(function () {
        Route::get('/',  'index')->name('dashboard');
        Route::get('/search',  'search')->name('search');
        Route::get('/riwayat-pengajuan',  'riwayatPengajuan')->name('riwayatPengajuan');
        Route::get('/kelola-pengusul',  'kelolapengusul')->name('kelolapengusul');
        Route::get('/jenis-surat',  'jenissurat')->name('kelolajenissurat');
        Route::post('/jenis-surat',  'store')->name('jenissurat.store');
        Route::put('/jenis-surat/{id}',  'update')->name('jenissurat.update');
        Route::delete('/jenis-surat/{id}',  'destroy')->name('jenissurat.destroy');
        Route::get('/pengusul/data',  'pengusulData')->name('pengusul.data');
        Route::get('/pengusul/{id}',  'getPengusul')->name('pengusul.get');
        Route::post('/pengusul',  'storePengusul')->name('pengusul.store');
        Route::put('/pengusul/{id}',  'updatePengusul')->name('pengusul.update');
        Route::delete('/pengusul/{id}',  'destroyPengusul')->name('pengusul.destroy');
        
        // Status Surat CRUD
        Route::get('/status-surat',  'kelolaStatusSurat')->name('kelolastatussurat');
        Route::post('/status-surat',  'storeStatusSurat')->name('statussurat.store');
        Route::put('/status-surat/{id}',  'updateStatusSurat')->name('statussurat.update');
        Route::delete('/status-surat/{id}',  'destroyStatusSurat')->name('statussurat.destroy');
    });

    // ======================= Kepala Sub =======================
    Route::middleware('role:kepala_sub')->prefix('kepala-sub')->controller(KepalaSubController::class)->group(function () {
        Route::get('/', 'index')->name('kepalasub.dashboard');
        Route::get('/dashboard/data', 'getDashboardData')->name('kepalasub.dashboard.data');
        Route::post('/surat/{id}/approve', 'approveSurat')->name('kepalasub.approve');
        Route::post('/surat/{id}/reject', 'rejectSurat')->name('kepalasub.reject');
        Route::get('/statistik','statistik')->name('kepalasub.statistik');
        Route::get('/persetujuansurat','persetujuansurat')->name('kepalasub.persetujuansurat');
        Route::get('/persetujuansurat/data','getSuratDiajukanData')->name('kepalasub.persetujuansurat.data');
        Route::get('/surat/{id}/tinjau','tinjauSurat')->name('kepalasub.tinjau-surat');
        Route::get('/riwayat-persetujuan', 'riwayatPersetujuan')->name('kepalasub.riwayat-persetujuan');
        Route::get('/riwayat-persetujuan/data',  'riwayatPersetujuanData')->name('kepala-sub.riwayat-persetujuan.data');


    });

    // ======================= Direktur =======================
    Route::middleware('role:direktur')->prefix('direktur')->controller(DirekturController::class)->group(function () {
        Route::get('/', 'index')->name('direktur.dashboard');
        Route::get('/dashboard/data', 'getDashboardData')->name('direktur.dashboard.data');
        Route::post('/surat/{id}/approve', 'approveSurat')->name('direktur.approve');
        Route::post('/surat/{id}/reject', 'rejectSurat')->name('direktur.reject');
        Route::get('/statistik','statistik')->name('direktur.statistik');
        Route::get('/persetujuansurat','persetujuansurat')->name('direktur.persetujuansurat');
        Route::get('/persetujuansurat/data','getSuratDiajukanData')->name('direktur.persetujuansurat.data');
        Route::get('/surat/{id}/tinjau','tinjauSurat')->name('direktur.tinjau-surat');
        Route::get('/riwayat-persetujuan', 'riwayatPersetujuan')->name('direktur.riwayat-persetujuan');
        Route::get('/riwayat-persetujuan/data',  'riwayatPersetujuanData')->name('direktur.riwayat-persetujuan.data');
    });
});



