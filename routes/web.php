<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\AnggaranKegiatanController;
use App\Http\Controllers\Web\PencairanDanaController;
use App\Http\Controllers\Web\RekapPengajuanController;
use App\Http\Controllers\Web\BukuCekController;
use App\Http\Controllers\Web\LpjKegiatanController;
use App\Http\Controllers\Web\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Guest Routes (Tidak perlu login)
|--------------------------------------------------------------------------
*/
Route::middleware(['guest'])->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Perlu login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |----------------------------------------------------------------------
    | Profile Management (All Users)
    |----------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('index');
        Route::put('/update', [UserController::class, 'updateProfile'])->name('update');
        Route::put('/change-password', [UserController::class, 'changePassword'])->name('change-password');
    });

    /*
    |----------------------------------------------------------------------
    | User Management (Admin Only)
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    /*
    |----------------------------------------------------------------------
    | Role Management (Admin Only)
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/assign-user', [RoleController::class, 'assignToUser'])->name('roles.assign-user');
        Route::delete('roles/{role}/remove-user/{user}', [RoleController::class, 'removeFromUser'])->name('roles.remove-user');
    });

    /*
    |----------------------------------------------------------------------
    | Anggaran Kegiatan
    |----------------------------------------------------------------------
    */
    Route::resource('anggaran-kegiatan', AnggaranKegiatanController::class);
    Route::prefix('anggaran-kegiatan')->name('anggaran-kegiatan.')->group(function () {
        Route::post('{anggaranKegiatan}/submit', [AnggaranKegiatanController::class, 'submit'])->name('submit');
        Route::get('{anggaranKegiatan}/timeline', [AnggaranKegiatanController::class, 'timeline'])->name('timeline');
        
        // Approval Routes (Role specific)
        Route::middleware(['role:kadiv,kadiv_umum,kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{anggaranKegiatan}/approve', [AnggaranKegiatanController::class, 'approve'])->name('approve');
            Route::post('{anggaranKegiatan}/reject', [AnggaranKegiatanController::class, 'reject'])->name('reject');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Pencairan Dana
    |----------------------------------------------------------------------
    */
    Route::resource('pencairan-dana', PencairanDanaController::class);
    Route::prefix('pencairan-dana')->name('pencairan-dana.')->group(function () {
        Route::post('{pencairanDana}/submit', [PencairanDanaController::class, 'submit'])->name('submit');
        
        // Approval Routes
        Route::middleware(['role:kadiv_umum,kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{pencairanDana}/approve', [PencairanDanaController::class, 'approve'])->name('approve');
            Route::post('{pencairanDana}/reject', [PencairanDanaController::class, 'reject'])->name('reject');
            Route::post('{pencairanDana}/disburse', [PencairanDanaController::class, 'disburse'])->name('disburse');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Rekap Pengajuan
    |----------------------------------------------------------------------
    */
    Route::resource('rekap-pengajuan', RekapPengajuanController::class);
    Route::prefix('rekap-pengajuan')->name('rekap-pengajuan.')->group(function () {
        Route::post('{rekapPengajuan}/submit', [RekapPengajuanController::class, 'submit'])->name('submit');
        
        // Approval Routes
        Route::middleware(['role:kadiv_umum,kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{rekapPengajuan}/approve', [RekapPengajuanController::class, 'approve'])->name('approve');
            Route::post('{rekapPengajuan}/reject', [RekapPengajuanController::class, 'reject'])->name('reject');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Buku Cek
    |----------------------------------------------------------------------
    */
    Route::resource('buku-cek', BukuCekController::class);
    Route::prefix('buku-cek')->name('buku-cek.')->group(function () {
        Route::middleware(['role:kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{bukuCek}/sign', [BukuCekController::class, 'sign'])->name('sign');
        });
        
        Route::middleware(['role:kadiv_umum,kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{bukuCek}/cash', [BukuCekController::class, 'cash'])->name('cash');
            Route::post('{bukuCek}/cancel', [BukuCekController::class, 'cancel'])->name('cancel');
        });
    });

    /*
    |----------------------------------------------------------------------
    | LPJ Kegiatan
    |----------------------------------------------------------------------
    */
    Route::resource('lpj-kegiatan', LpjKegiatanController::class);
    Route::prefix('lpj-kegiatan')->name('lpj-kegiatan.')->group(function () {
        Route::post('{lpjKegiatan}/submit', [LpjKegiatanController::class, 'submit'])->name('submit');
        
        // Approval Routes
        Route::middleware(['role:kadiv_umum,kepala_jic,super_admin,admin'])->group(function () {
            Route::post('{lpjKegiatan}/approve', [LpjKegiatanController::class, 'approve'])->name('approve');
            Route::post('{lpjKegiatan}/reject', [LpjKegiatanController::class, 'reject'])->name('reject');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Document Management
    |----------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('upload', [DocumentController::class, 'upload'])->name('upload');
        Route::get('{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('{document}/view', [DocumentController::class, 'view'])->name('view');
        Route::delete('{document}', [DocumentController::class, 'destroy'])->name('destroy');
    });
});