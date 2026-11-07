<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\LoginSecurityController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemAuditController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])
            ->middleware('throttle:admin-login')->name('login.store');
        Route::get('/password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('/password/forgot', [PasswordResetLinkController::class, 'store'])->middleware('throttle:admin-password')->name('password.email');
        Route::get('/password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/password/reset', [NewPasswordController::class, 'store'])->middleware('throttle:admin-password')->name('password.update');
    });

    Route::middleware(['auth', 'account.active', 'session.current'])->group(function (): void {
        Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
        Route::get('/settings', SettingsController::class)->name('settings.index');
        Route::get('/system-audit', SystemAuditController::class)->middleware('can:system.audit.view')->name('system-audit.index');
        Route::get('/system-status', SystemStatusController::class)->middleware('can:system.audit.view')->name('system-status.index');
        Route::get('/system-audit/login-security', LoginSecurityController::class)
            ->middleware('can:security.login.view')->name('login-security.index');
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
        Route::get('/notifications', [NotificationController::class, 'index'])->middleware('can:notifications.view')->name('notifications.index');
        Route::get('/notifications/{outboxMessage}', [NotificationController::class, 'show'])->middleware('can:notifications.view')->name('notifications.show');
        Route::post('/notifications/{outboxMessage}/retry', [NotificationController::class, 'retry'])->middleware('can:notifications.retry')->name('notifications.retry');

        Route::get('/documents', [DocumentController::class, 'index'])->middleware('can:documents.view')->name('documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])->middleware('can:documents.upload')->name('documents.store');
        Route::get('/documents/{document:uuid}/download', [DocumentController::class, 'download'])->middleware('can:documents.download')->name('documents.download');
        Route::put('/documents/{document:uuid}', [DocumentController::class, 'update'])->middleware('can:documents.update')->name('documents.update');
        Route::delete('/documents/{document:uuid}', [DocumentController::class, 'destroy'])->middleware('can:documents.delete')->name('documents.destroy');
    });
});
