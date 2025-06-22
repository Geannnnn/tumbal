<?php

use App\Http\Controllers\adminController;
use App\Http\Controllers\AuthController;
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


Route::middleware(['multi-auth'])->group(function () {
    // ======================= Pengusul =======================
    Route::middleware('role:pengusul')->group(function () {
        // Mahasiswa
        Route::prefix('mahasiswa')->controller(mahasiswaController::class)->group(function () {
            Route::get('/', 'index')->name('mahasiswa.dashboard');
            Route::get('/index/data','data')->name('surat.data');
            Route::get('/pengajuan', 'pengajuan')->name('mahasiswa.pengajuansurat');
            Route::get('/search', 'search')->name('mahasiswa.search');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('mahasiswa.surat.edit');
            Route::put('/surat/{id}', [SuratController::class, 'update'])->name('mahasiswa.surat.update');
            Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('mahasiswa.surat.destroy');
            Route::get('/draft/data','draftData')->name('mahasiswa.draftData');
            Route::get('/draft', 'draft')->name('mahasiswa.draft');
            Route::get('/status', 'status')->name('mahasiswa.statussurat');
            Route::get('/status/data', 'getStatusSuratData')->name('statusSurat.data');
            Route::get('/setting', 'setting')->name('mahasiswa.setting');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('mahasiswa.statussurat.show');

        });

        // Dosen
        Route::prefix('dosen')->controller(dosenController::class)->group(function () {
            Route::get('/', 'index')->name('dosen.dashboard');
            Route::get('/index/data','data')->name('surat.data');
            Route::get('/pengajuan', 'pengajuan')->name('dosen.pengajuansurat');
            Route::get('/search', 'search')->name('dosen.search');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('dosen.surat.edit');
            Route::put('/surat/{id}', [SuratController::class, 'update'])->name('dosen.surat.update');
            Route::delete('/surat/{id}', [SuratController::class, 'destroy'])->name('dosen.surat.destroy');
            Route::get('/draft/data','draftData')->name('dosen.draftData');
            Route::get('/draft', 'draft')->name('dosen.draft');
            Route::get('/status', 'status')->name('dosen.statussurat');
            Route::get('/status/data', 'getStatusSuratData')->name('statusSurat.data');
            Route::get('/setting', 'setting')->name('dosen.setting');
            Route::get('/statussurat/{id}', 'showStatusSurat')->name('dosen.statussurat.show');
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
            Route::get('/jenissurat','jenissurat')->name('staffumum.jenissurat');
            Route::get('/tinjau-surat', 'tinjauSurat')->name('staffumum.tinjausurat');
            Route::get('/tinjau-surat/data', 'getSuratData')->name('staffumum.tinjau.data');
            Route::get('/surat/{id}/tinjau', 'showDetailSurat')->name('staffumum.tinjau.detail');
        });

        Route::prefix('tata-usaha')->controller(tatausahaController::class)->group(function () {
            Route::get('/', 'index')->name('tatausaha.dashboard');
            Route::get('/statistik','statistik')->name('tatausaha.statistik');
            Route::get('/terbitkan','terbitkan')->name('tatausaha.terbitkan');
            Route::get('/statussurat','statussurat')->name('tatausaha.statussurat');
            Route::get('/jenissurat','jenissurat')->name('tatausaha.jenissurat');
            Route::get('/tinjau-surat','tinjauSurat')->name('tatausaha.tinjau-surat');
        });
    });

    // ======================= Admin =======================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [adminController::class, 'index'])->name('dashboard');
        Route::get('/kelola-pengusul', [adminController::class, 'kelolapengusul'])->name('kelolapengusul');
        Route::get('/jenis-surat', [adminController::class, 'jenissurat'])->name('kelolajenissurat');
        Route::post('/jenis-surat', [adminController::class, 'store'])->name('jenissurat.store');
        Route::put('/jenis-surat/{id}', [adminController::class, 'update'])->name('jenissurat.update');
        Route::delete('/jenis-surat/{id}', [adminController::class, 'destroy'])->name('jenissurat.destroy');
        Route::get('/pengusul/data', [adminController::class, 'pengusulData'])->name('pengusul.data');
        Route::get('/pengusul/{id}', [adminController::class, 'getPengusul'])->name('pengusul.get');
        Route::post('/pengusul', [adminController::class, 'storePengusul'])->name('pengusul.store');
        Route::put('/pengusul/{id}', [adminController::class, 'updatePengusul'])->name('pengusul.update');
        Route::delete('/pengusul/{id}', [adminController::class, 'destroyPengusul'])->name('pengusul.destroy');
        
        // Status Surat CRUD
        Route::get('/status-surat', [adminController::class, 'kelolaStatusSurat'])->name('kelolastatussurat');
        Route::post('/status-surat', [adminController::class, 'storeStatusSurat'])->name('statussurat.store');
        Route::put('/status-surat/{id}', [adminController::class, 'updateStatusSurat'])->name('statussurat.update');
        Route::delete('/status-surat/{id}', [adminController::class, 'destroyStatusSurat'])->name('statussurat.destroy');
    });

    // ======================= Kepala Sub =======================
    Route::middleware('role:kepala_sub')->prefix('kepala-sub')->controller(KepalaSubController::class)->group(function () {
        Route::get('/', 'index')->name('kepalasub.dashboard');
        Route::get('/statistik','statistik')->name('kepalasub.statistik');
        Route::get('/persetujuansurat','persetujuansurat')->name('kepalasub.persetujuansurat');
        Route::get('/persetujuansurat/data','getSuratDiajukanData')->name('kepalasub.persetujuansurat.data');
        Route::get('/surat/{id}/tinjau','tinjauSurat')->name('kepalasub.tinjau-surat');
    });
});
