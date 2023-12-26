<?php

namespace App\Providers;

use App\Services\Mikrotik\Hotspot;
use App\Services\Ypareo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Hotspot::class, function ($app) {
            return new Hotspot(config('services.mikrotik.baseUrl'), config('services.mikrotik.username'), config('services.mikrotik.password'));
        });

        $this->app->bind(Ypareo::class, function ($app) {
            return new Ypareo(config('services.ypareo.apiKey'), config('services.ypareo.baseUrl'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
