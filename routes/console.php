<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| The cron entry on your server should be:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Process queued jobs (AI RFQ processing, emails, etc.)
// Runs every minute to ensure RFQs are picked up quickly
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Refresh Zoho OAuth token every 45 minutes
// (Zoho tokens expire after 60 minutes — this keeps them fresh)
Schedule::call(function () {
    try {
        app(\App\Services\Zoho\ZohoTokenService::class)->getAccessToken();
        \Illuminate\Support\Facades\Log::info('Zoho token refreshed successfully by scheduler.');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Scheduled Zoho token refresh failed: ' . $e->getMessage());
    }
})->cron('*/45 * * * *')->name('zoho-token-refresh')->withoutOverlapping();
