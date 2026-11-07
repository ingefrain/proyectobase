<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('outbox:process --limit=100')->everyMinute()->withoutOverlapping(1);
if (config('security.login_attempt_schedule_prune', true)) {
    Schedule::command('security:prune-login-attempts')->daily()->withoutOverlapping();
}
