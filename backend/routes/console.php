<?php

use App\Services\CheckoutService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Run via `php artisan schedule:work` (foreground, container friendly) or
| the system cron entry `* * * * * php artisan schedule:run >> /dev/null 2>&1`.
|
*/

Schedule::call(function () {
    app(CheckoutService::class)->expireStaleReservations();
})->everyMinute()
    ->name('checkout:expire-reservations')
    ->withoutOverlapping()
    ->onFailure(function (Throwable $e) {
        report($e);
    })
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::channel('stack')->debug('checkout:expire-reservations completed');
    });