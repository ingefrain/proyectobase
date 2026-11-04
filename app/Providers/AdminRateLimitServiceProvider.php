<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AdminRateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request): array {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(5)->by('admin-login:account:'.hash('sha256', $email)),
                Limit::perMinute(20)->by('admin-login:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('admin-password', fn (Request $request) => Limit::perMinute(5)
            ->by('admin-password:ip:'.$request->ip()));
    }
}
