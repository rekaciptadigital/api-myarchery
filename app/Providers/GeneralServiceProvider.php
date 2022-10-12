<?php

namespace App\Providers;

use App\BLoC\General\GetCityCountry;
use App\BLoC\General\GetCountry;
use Illuminate\Support\ServiceProvider;

class GeneralServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService("getEventClubRanked", GetEventClubRanked::class);
        $this->registerService("getCityCountry", GetCityCountry::class);
        $this->registerService("getCountry", GetCountry::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
