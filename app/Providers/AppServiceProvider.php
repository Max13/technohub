<?php

namespace App\Providers;

use App\Services\Mikrotik\Hotspot;
use App\Services\Wallet;
use App\Services\Ypareo;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

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

        $this->app->bind(Wallet::class, function ($app, array $parameters) {
            $class = 'App\Services\Wallet\\' . ucfirst($parameters[0]);

            throw_if(
                !class_exists($class),
                RuntimeException::class,
                'Service Container cannot find Wallet class for platform: ' . $parameters[0] . '.'
            );

            return new $class(...array_values(config("services.$parameters[0].wallet")));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::defaultView('components.pagination');
    }
}
