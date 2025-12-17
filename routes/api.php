<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AnggaranKegiatanController;
use App\Http\Controllers\Api\PencairanDanaController;
use App\Http\Controllers\Api\RekapPengajuanController;
use App\Http\Controllers\Api\BukuCekController;
use App\Http\Controllers\Api\LpjKegiatanController;
use App\Http\Controllers\Api\DocumentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
    
    /*
    |----------------------------------------------------------------------
    | Authentication Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    /*
    |----------------------------------------------------------------------
    | User Management Routes (Admin Only)
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::prefix('users')->group(function () {
            // Must be before resource routes
            Route::get('/list', [UserController::class, 'list']);
            Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
            Route::get('/{id}/statistics', [UserController::class, 'statistics']);
        });
        
        Route::apiResource('users', UserController::class);
    });

    /*
    |----------------------------------------------------------------------
    | User Profile Routes (All Authenticated Users)
    |----------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::put('/update', [UserController::class, 'updateProfile']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });

    /*
    |----------------------------------------------------------------------
    | Role Management Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('roles')->group(function () {
        // Public endpoints (all authenticated users can view)
        Route::get('/list', [RoleController::class, 'list']);
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::get('/{id}/statistics', [RoleController::class, 'statistics']);
        
        // Admin only endpoints
        Route::middleware(['role:super_admin,admin'])->group(function () {
            Route::post('/', [RoleController::class, 'store']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);
            
            // Role assignment
            Route::post('/assign', [RoleController::class, 'assignToUser']);
            Route::post('/remove', [RoleController::class, 'removeFromUser']);
            Route::post('/sync', [RoleController::class, 'syncUserRoles']);
        });
    });

    /*
    |----------------------------------------------------------------------
    | Anggaran Kegiatan Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('anggaran-kegiatan')->group(function () {
        // Basic CRUD - All authenticated users
        Route::get('/', [AnggaranKegiatanController::class, 'index']);
        Route::post('/', [AnggaranKegiatanController::class, 'store']);
        Route::get('/statistics', [AnggaranKegiatanController::class, 'statistics']);
        Route::get('/{id}', [AnggaranKegiatanController::class, 'show']);
        Route::put('/{id}', [AnggaranKegiatanController::class, 'update']);
        Route::delete('/{id}', [AnggaranKegiatanController::class, 'destroy']);
        
        // Workflow actions
        Route::post('/{id}/submit', [AnggaranKegiatanController::class, 'submit']);
        Route::get('/{id}/timeline', [AnggaranKegiatanController::class, 'timeline']);
        
        // Approval actions - Role specific
        Route::post('/{id}/approve', [AnggaranKegiatanController::class, 'approve'])
            ->middleware('role:kadiv,kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/reject', [AnggaranKegiatanController::class, 'reject'])
            ->middleware('role:kadiv,kadiv_umum,kepala_jic,super_admin,admin');
    });

    /*
    |----------------------------------------------------------------------
    | Pencairan Dana Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('pencairan-dana')->group(function () {
        // Basic CRUD
        Route::get('/', [PencairanDanaController::class, 'index']);
        Route::post('/', [PencairanDanaController::class, 'store']);
        Route::get('/statistics', [PencairanDanaController::class, 'statistics']);
        Route::get('/{id}', [PencairanDanaController::class, 'show']);
        Route::put('/{id}', [PencairanDanaController::class, 'update']);
        Route::delete('/{id}', [PencairanDanaController::class, 'destroy']);
        
        // Workflow actions
        Route::post('/{id}/submit', [PencairanDanaController::class, 'submit']);
        
        // Approval actions - Role specific
        Route::post('/{id}/approve', [PencairanDanaController::class, 'approve'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/reject', [PencairanDanaController::class, 'reject'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/disburse', [PencairanDanaController::class, 'disburse'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
    });

    /*
    |----------------------------------------------------------------------
    | Rekap Pengajuan Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('rekap-pengajuan')->group(function () {
        // Basic CRUD
        Route::get('/', [RekapPengajuanController::class, 'index']);
        Route::post('/', [RekapPengajuanController::class, 'store']);
        Route::get('/statistics', [RekapPengajuanController::class, 'statistics']);
        Route::get('/{id}', [RekapPengajuanController::class, 'show']);
        Route::put('/{id}', [RekapPengajuanController::class, 'update']);
        Route::delete('/{id}', [RekapPengajuanController::class, 'destroy']);
        
        // Workflow actions
        Route::post('/{id}/submit', [RekapPengajuanController::class, 'submit']);
        
        // Approval actions
        Route::post('/{id}/approve', [RekapPengajuanController::class, 'approve'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/reject', [RekapPengajuanController::class, 'reject'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
    });

    /*
    |----------------------------------------------------------------------
    | Buku Cek Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('buku-cek')->group(function () {
        // Basic CRUD
        Route::get('/', [BukuCekController::class, 'index']);
        Route::post('/', [BukuCekController::class, 'store']);
        Route::get('/statistics', [BukuCekController::class, 'statistics']);
        Route::get('/{id}', [BukuCekController::class, 'show']);
        Route::put('/{id}', [BukuCekController::class, 'update']);
        Route::delete('/{id}', [BukuCekController::class, 'destroy']);
        
        // Check workflow actions
        Route::post('/{id}/sign', [BukuCekController::class, 'sign'])
            ->middleware('role:kepala_jic,super_admin,admin');
            
        Route::post('/{id}/cash', [BukuCekController::class, 'cash'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/cancel', [BukuCekController::class, 'cancel'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
    });

    /*
    |----------------------------------------------------------------------
    | LPJ Kegiatan Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('lpj-kegiatan')->group(function () {
        // Basic CRUD
        Route::get('/', [LpjKegiatanController::class, 'index']);
        Route::post('/', [LpjKegiatanController::class, 'store']);
        Route::get('/statistics', [LpjKegiatanController::class, 'statistics']);
        Route::get('/{id}', [LpjKegiatanController::class, 'show']);
        Route::put('/{id}', [LpjKegiatanController::class, 'update']);
        Route::delete('/{id}', [LpjKegiatanController::class, 'destroy']);
        
        // Workflow actions
        Route::post('/{id}/submit', [LpjKegiatanController::class, 'submit']);
        
        // Approval actions
        Route::post('/{id}/approve', [LpjKegiatanController::class, 'approve'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
            
        Route::post('/{id}/reject', [LpjKegiatanController::class, 'reject'])
            ->middleware('role:kadiv_umum,kepala_jic,super_admin,admin');
    });

    /*
    |----------------------------------------------------------------------
    | Document Management Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/upload', [DocumentController::class, 'upload']);
        Route::post('/upload-multiple', [DocumentController::class, 'uploadMultiple']);
        Route::get('/statistics', [DocumentController::class, 'statistics']);
        Route::get('/{id}', [DocumentController::class, 'show']);
        Route::get('/{id}/download', [DocumentController::class, 'download']);
        Route::get('/{id}/view', [DocumentController::class, 'view']);
        Route::delete('/{id}', [DocumentController::class, 'destroy']);
    });
});