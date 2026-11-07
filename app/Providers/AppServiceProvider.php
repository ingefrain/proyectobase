<?php

namespace App\Providers;

use App\Contracts\OutboxEmailSender;
use App\Mail\LaravelOutboxEmailSender;
use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Support\BasePermissions;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OutboxEmailSender::class, LaravelOutboxEmailSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Document::class, DocumentPolicy::class);

        foreach (array_keys(BasePermissions::DEFINITIONS) as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->hasPermissionTo($permission));
        }

        ResetPassword::createUrlUsing(fn (User $user, string $token): string => route('admin.password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));
    }
}
