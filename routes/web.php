<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\AvatarController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LoginSecurityController;
use App\Http\Controllers\Admin\MarkdownPreviewController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\OrganizationLogoController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SystemAuditController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureComponentCatalogEnabled;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));
Route::get('/branding/logo', [BrandingController::class, 'logo'])->name('branding.logo');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])
            ->name('login.store');
        Route::get('/password/forgot', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('/password/forgot', [PasswordResetLinkController::class, 'store'])->middleware('throttle:admin-password')->name('password.email');
        Route::get('/password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/password/reset', [NewPasswordController::class, 'store'])->middleware('throttle:admin-password')->name('password.update');
    });

    Route::middleware(['auth', 'impersonation', 'account.active', 'session.current'])->group(function (): void {
        Route::post('/markdown/preview', MarkdownPreviewController::class)->name('markdown.preview');
        Route::prefix('components')->name('components.')
            ->middleware([EnsureComponentCatalogEnabled::class, 'can:system.audit.view'])
            ->group(function (): void {
                Route::view('/', 'admin.components.index')->name('index');
                foreach (['markdown', 'dropzone', 'password', 'modal', 'multi-select', 'confirmations'] as $demo) {
                    Route::view($demo, 'admin.components.'.$demo)->name($demo);
                }
            });
        Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
        Route::get('/settings', SettingsController::class)->name('settings.index');
        Route::get('/system-audit', SystemAuditController::class)->middleware('can:system.audit.view')->name('system-audit.index');
        Route::get('/system-status', SystemStatusController::class)->middleware('can:system.audit.view')->name('system-status.index');
        Route::get('/system-audit/login-security', LoginSecurityController::class)
            ->middleware('can:security.login.view')->name('login-security.index');
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
        Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])->name('impersonation.stop');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
        Route::get('/profile/avatar', [AvatarController::class, 'own'])->name('profile.avatar');

        Route::get('/branding', [BrandingController::class, 'edit'])->middleware('can:branding.manage')->name('branding.edit');
        Route::put('/branding', [BrandingController::class, 'update'])->middleware('can:branding.manage')->name('branding.update');
        Route::delete('/branding/logo', [BrandingController::class, 'destroyLogo'])->middleware('can:branding.manage')->name('branding.logo.delete');
        Route::get('/users/{user}/avatar', [AvatarController::class, 'user'])->middleware('can:users.view')->name('users.avatar');

        Route::get('/users', [UserController::class, 'index'])->middleware('can:users.view')->name('users.index');
        Route::post('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->middleware('can:users.impersonate')->name('users.impersonate');
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

        Route::get('/organizations', [OrganizationController::class, 'index'])->middleware('can:organizations.view')->name('organizations.index');
        Route::get('/organizations/create', [OrganizationController::class, 'create'])->middleware('can:organizations.create')->name('organizations.create');
        Route::post('/organizations', [OrganizationController::class, 'store'])->middleware('can:organizations.create')->name('organizations.store');
        Route::get('/organizations/{organization}/logo', OrganizationLogoController::class)->middleware('can:organizations.view')->name('organizations.logo');
        Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->middleware('can:organizations.update')->name('organizations.edit');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->middleware('can:organizations.update')->name('organizations.update');
        Route::delete('/organizations/{organization}/logo', [OrganizationController::class, 'deleteLogo'])->middleware('can:organizations.update')->name('organizations.logo.delete');
        Route::patch('/organizations/{organization}/status', [OrganizationController::class, 'toggleStatus'])->middleware('can:organizations.disable')->name('organizations.status');

        Route::get('/locations', [LocationController::class, 'index'])->middleware('can:locations.view')->name('locations.index');
        Route::get('/locations/create', [LocationController::class, 'create'])->middleware('can:locations.create')->name('locations.create');
        Route::post('/locations', [LocationController::class, 'store'])->middleware('can:locations.create')->name('locations.store');
        Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->middleware('can:locations.update')->name('locations.edit');
        Route::put('/locations/{location}', [LocationController::class, 'update'])->middleware('can:locations.update')->name('locations.update');
        Route::patch('/locations/{location}/status', [LocationController::class, 'toggleStatus'])->middleware('can:locations.disable')->name('locations.status');
    });
});
