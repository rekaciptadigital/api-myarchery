<?php

namespace App\Providers;

use App\BLoC\App\ArcheryEventParticipant\FindParticipantDetail;
use App\BLoC\App\ArcheryEventParticipant\EditParticipantProfile;
use Illuminate\Support\ServiceProvider;

use App\BLoC\App\UserAuth\UserForgotPassword;
use App\BLoC\App\UserAuth\UserLogin;
use App\BLoC\App\UserAuth\UserRegister;
use App\BLoC\App\UserAuth\UserResetPassword;
use App\BLoC\App\UserAuth\GetUserProfile;
use App\BLoC\App\UserAuth\UserLogout;
use App\BLoC\App\Certificate\GetListDownloadCertificate;
use App\BLoC\App\Certificate\GetDownload;

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
        $this->registerService("getParticipantScoreSummary", GetParticipantScoreSummary::class);
        $this->registerService("getListDownloadCertificate", GetListDownloadCertificate::class);
        $this->registerService("getDownload", GetDownload::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
