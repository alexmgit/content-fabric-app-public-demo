<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use App\Listeners\UserEventSubscriber;
use Illuminate\Support\Facades\Event;
use App\Contracts\Clock;
use App\Contracts\FeatureFlags;
use App\Contracts\Logger;
use App\Contracts\Mailer;
use App\Contracts\Sleeper;
use App\Contracts\TransactionManager;
use App\Services\Adapters\ConfigFeatureFlags;
use App\Services\Adapters\LaravelClock;
use App\Services\Adapters\LaravelLogger;
use App\Services\Adapters\LaravelMailer;
use App\Services\Adapters\LaravelTransactionManager;
use App\Services\Adapters\SystemSleeper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Clock::class, LaravelClock::class);
        $this->app->bind(FeatureFlags::class, ConfigFeatureFlags::class);
        $this->app->bind(Logger::class, LaravelLogger::class);
        $this->app->bind(Mailer::class, LaravelMailer::class);
        $this->app->bind(Sleeper::class, SystemSleeper::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_HTTPS_DISABLE') !== true)
        {
            $this->app['request']->server->set('HTTPS', true);
        }

        LogViewer::auth(function ($request) {
            return $request->user()?->canAccessLogViewer();
        });
    }
}
