<?php

namespace App\Providers;

use App\BLoC\App\ArcheryEventParticipant\FindParticipantDetail;
use App\BLoC\App\ArcheryEventParticipant\AddParticipantScore;
use App\BLoC\App\ArcheryEventParticipant\EditParticipantProfile;
use App\BLoC\App\ArcheryEventParticipant\GetEnd;
use App\BLoC\App\ArcheryEventParticipant\GetEndDetail;
use App\BLoC\App\ArcheryEventParticipant\GetScoreSummary;
use Illuminate\Support\ServiceProvider;

use App\BLoC\App\UserAuth\UserForgotPassword;
use App\BLoC\App\UserAuth\UserLogin;
use App\BLoC\App\UserAuth\UserRegister;
use App\BLoC\App\UserAuth\UserResetPassword;
use App\BLoC\App\UserAuth\GetUserProfile;
use App\BLoC\App\UserAuth\UserLogout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService("userForgotPassword", UserForgotPassword::class);
        $this->registerService("userLogin", UserLogin::class);
        $this->registerService("userRegister", UserRegister::class);
        $this->registerService("userResetPassword", UserResetPassword::class);
        $this->registerService("getUserProfile", GetUserProfile::class);
        $this->registerService("userLogout", UserLogout::class);
        $this->registerService("findParticipantDetail", FindParticipantDetail::class);
        $this->registerService("addParticipantScore", AddParticipantScore::class);
        $this->registerService("editParticipantProfile", EditParticipantProfile::class);
        $this->registerService("getEnd", GetEnd::class);
        $this->registerService("getEndDetail", GetEndDetail::class);
        $this->registerService("getScoreSummary", GetScoreSummary::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
