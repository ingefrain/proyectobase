<?php

namespace App\Providers;

use App\Models\LoginAttempt;
use App\Services\LoginAttemptRecorder;
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
            $recordLimitedAttempt = function (Request $request, array $headers) {
                app(LoginAttemptRecorder::class)->record(
                    $request,
                    LoginAttempt::RATE_LIMITED,
                    'rate_limit_exceeded',
                );

                return response('Demasiados intentos. Intenta de nuevo más tarde.', 429, $headers);
            };

            return [
                Limit::perMinute(5)->by('admin-login:account:'.hash('sha256', $email))->response($recordLimitedAttempt),
                Limit::perMinute(20)->by('admin-login:ip:'.$request->ip())->response($recordLimitedAttempt),
            ];
        });

        RateLimiter::for('admin-password', fn (Request $request) => Limit::perMinute(5)
            ->by('admin-password:ip:'.$request->ip()));
    }
}
