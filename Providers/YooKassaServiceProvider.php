<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use YooKassa\Client;

class YooKassaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //

        $this->app->singleton(Client::class, function ($app) {
            $client = new Client();
            if (config('services.yookassa.shop_id') && config('services.yookassa.secret_key'))
            {
                $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));
            }
            return $client;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
