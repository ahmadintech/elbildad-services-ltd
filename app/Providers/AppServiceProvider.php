<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\Item;
use App\Observers\UserObserver;
use App\Observers\ItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Register Zoho Sync Observers
        User::observe(UserObserver::class);
        Item::observe(ItemObserver::class);

        // Track last login and notify admins for agent authentication events
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event) {
            if ($event->user) {
                $event->user->forceFill([
                    'last_login_at' => now(),
                ])->saveQuietly();

                try {
                    app(\App\Services\AgentActivityNotificationService::class)->recordLogin(
                        $event->user,
                        request()?->ip(),
                        request()?->userAgent()
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Agent login notification error: ' . $e->getMessage());
                }
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function (\Illuminate\Auth\Events\Logout $event) {
            if ($event->user) {
                try {
                    app(\App\Services\AgentActivityNotificationService::class)->recordLogout(
                        $event->user,
                        request()?->ip(),
                        request()?->userAgent()
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Agent logout notification error: ' . $e->getMessage());
                }
            }
        });
    }
}
