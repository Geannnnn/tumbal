<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\dosenController;
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
Route::post('/awdawdawd',function(){
    return view('auth.lostpassword');
})->name('lostpassword');
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
Route::get('/pengajuan/detail/{id}', [SuratController::class, 'show'])->name('surat.detail');
Route::delete('/surat/{id}',[SuratController::class, 'destroy'])->name('surat.destroy');
Route::put('/surat/{id}', [SuratController::class, 'update'])->name('surat.update');


Route::middleware(['multi-auth'])->group(function () {

    // ======================= Pengusul =======================
    Route::middleware('role:pengusul')->group(function () {
        // Mahasiswa
        Route::prefix('mahasiswa')->controller(mahasiswaController::class)->group(function () {
            Route::get('/', 'index')->name('mahasiswa.dashboard');
            Route::get('/index/data','data')->name('surat.data');
            Route::get('/pengajuan', 'pengajuan')->name('mahasiswa.pengajuansurat');
            Route::get('/search', 'search')->name('mahasiswa.search');
            Route::get('/surat/{id}/edit', [SuratController::class, 'edit'])->name('surat.edit');
            Route::get('/draft/data','draftData')->name('mahasiswa.draftData');
            Route::get('/pengajuan/search', 'pengajuansearch')->name('mahasiswa.pengajuansearch');
            Route::get('/pengajuan/data','pengajuandata')->name('pengajuan.data');
            Route::get('/draft', 'draft')->name('mahasiswa.draft');
            Route::get('/status', 'status')->name('mahasiswa.statussurat');

        });

        // Dosen
        Route::prefix('dosen')->controller(dosenController::class)->group(function () {
            Route::get('/', 'index')->name('dosen.dashboard');
            Route::get('/pengajuan', 'pengajuan')->name('dosen.pengajuansurat');
            // Route::get('/pengajuan/surat', 'pengajuanshow')->name('dosen.pengajuan');
            Route::get('/draft', 'draft')->name('dosen.draft');
            Route::get('/status', 'status')->name('dosen.statussurat');
        });
    });

    // ======================= Staff =======================
    Route::middleware('role:staff')->group(function () {
        Route::prefix('staff-umum')->controller(staffumumController::class)->group(function () {
            Route::get('/', 'index')->name('staffumum.dashboard');
            Route::get('/search', 'search')->name('staffumum.search');

        });

        Route::prefix('tata-usaha')->controller(tatausahaController::class)->group(function () {
            Route::get('/', 'index')->name('tatausaha.dashboard');
        });
    });
});


Route::post('/test',[testController::class,'test'])->name('test');
Route::post('/aju',[testController::class,'aju'])->name('aju');// Route::middleware('auth')->group(function () { // Middleware auth untuk memastikan user sudah login
