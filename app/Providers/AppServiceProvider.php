<?php

namespace App\Providers;

use App\BLoC\General\ForgotPassword;
use App\BLoC\General\GetUserProfile;
use App\BLoC\General\Login;
use App\BLoC\General\Logout;
use App\BLoC\General\Register;
use App\BLoC\General\ResetPassword;
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
        $this->registerService("login", Login::class);
        $this->registerService("logout", Logout::class);
        $this->registerService("register", Register::class);
        $this->registerService("forgotPassword", ForgotPassword::class);
        $this->registerService("resetPassword", ResetPassword::class);
        $this->registerService("getUserProfile", GetUserProfile::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }

}
