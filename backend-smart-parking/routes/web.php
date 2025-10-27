<?php

use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\Admin\ParkingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Middleware\AdminAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthUserController;


Route::prefix('/user')->group(function () {
    Route::get('redirect/google', [AuthUserController::class, 'redirectToGoogle'])->name('user.google.redirect');
    Route::get('/login', [AuthUserController::class, 'index'])->name('user.login.form');
    Route::post('/logout', [AuthUserController::class, 'logout'])->name('user.logout');
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/history', [DashboardController::class, 'getHistoryParking'])->name('user.dashboard.history');
    })->middleware('auth');
});

Route::get('/auth/callback', [AuthUserController::class, 'login'])->name('user.login');


Route::redirect('/', '/user/login');


Route::prefix('admin')->group(function () {
    Route::get('login', [AuthAdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AuthAdminController::class, 'login'])->name('admin.login.post');
    Route::post('logout', [AuthAdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(AdminAuth::class)->group(function () {
        Route::get('dashboard', [AuthAdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('setting', [SettingController::class, 'index'])->name('admin.setting');
        Route::post('setting', [SettingController::class, 'update'])->name('admin.setting.update');
        Route::get('/parking', [ParkingController::class, 'index'])->name('admin.parking.index');
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/report', [ReportController::class, 'index'])->name('admin.report.index');
        Route::post('/parking/reset', [ParkingController::class, 'resetData'])->name('admin.parking.reset');
    });
});
