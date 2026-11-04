<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
        Route::get('/password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('/password/forgot', [PasswordResetLinkController::class, 'store'])->middleware('throttle:admin-password')->name('password.email');
        Route::get('/password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/password/reset', [NewPasswordController::class, 'store'])->middleware('throttle:admin-password')->name('password.update');
    });

    Route::middleware(['auth', 'account.active', 'session.current'])->group(function (): void {
        Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/users', [UserController::class, 'index'])->middleware('can:users.view')->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->middleware('can:users.create')->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.update')->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.update')->name('users.update');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->middleware('can:users.disable')->name('users.status');

        Route::get('/roles', [RoleController::class, 'index'])->middleware('can:roles.view')->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->middleware('can:roles.create')->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('can:roles.create')->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('can:roles.update')->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.update')->name('roles.update');
        Route::patch('/roles/{role}/status', [RoleController::class, 'toggleStatus'])->middleware('can:roles.update')->name('roles.status');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
            ->middleware(['can:roles.update', 'can:permissions.assign'])->name('roles.permissions');

        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('can:permissions.view')->name('permissions.index');
        Route::get('/audit', [AuditController::class, 'index'])->middleware('can:audit.view')->name('audit.index');
    });
});
