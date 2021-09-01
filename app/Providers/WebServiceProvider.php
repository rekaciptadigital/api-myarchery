<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\BLoC\Web\AdminAuth\ForgotPassword;
use App\BLoC\Web\AdminAuth\Login;
use App\BLoC\Web\AdminAuth\Register;
use App\BLoC\Web\AdminAuth\ResetPassword;
use App\BLoC\Web\AdminAuth\GetProfile;
use App\BLoC\Web\AdminAuth\Logout;
use App\BLoC\Web\ArcheryAgeCategory\EditArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\FindArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\DeleteArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\BulkDeleteArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\GetArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\AddArcheryAgeCategory;
use App\BLoC\Web\ArcheryCategory\DeleteArcheryCategory;
use App\BLoC\Web\ArcheryCategory\BulkDeleteArcheryCategory;
use App\BLoC\Web\ArcheryCategory\FindArcheryCategory;
use App\BLoC\Web\ArcheryCategory\AddArcheryCategory;
use App\BLoC\Web\ArcheryCategory\EditArcheryCategory;
use App\BLoC\Web\ArcheryCategory\GetArcheryCategory;
use App\BLoC\Web\ArcheryClub\BulkDeleteArcheryClub;
use App\BLoC\Web\ArcheryClub\FindArcheryClub;
use App\BLoC\Web\ArcheryClub\DeleteArcheryClub;
use App\BLoC\Web\ArcheryClub\EditArcheryClub;
use App\BLoC\Web\ArcheryClub\AddArcheryClub;
use App\BLoC\Web\ArcheryClub\GetArcheryClub;
use App\BLoC\Web\ArcheryEvent\EditArcheryEvent;
use App\BLoC\Web\ArcheryEvent\DeleteArcheryEvent;
use App\BLoC\Web\ArcheryEvent\GetArcheryEvent;
use App\BLoC\Web\ArcheryEvent\AddArcheryEvent;
use App\BLoC\Web\ArcheryEvent\FindArcheryEvent;

class WebServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService("forgotPassword", ForgotPassword::class);
        $this->registerService("login", Login::class);
        $this->registerService("register", Register::class);
        $this->registerService("resetPassword", ResetPassword::class);
        $this->registerService("getProfile", GetProfile::class);
        $this->registerService("logout", Logout::class);
        $this->registerService("editArcheryAgeCategory", EditArcheryAgeCategory::class);
        $this->registerService("findArcheryAgeCategory", FindArcheryAgeCategory::class);
        $this->registerService("deleteArcheryAgeCategory", DeleteArcheryAgeCategory::class);
        $this->registerService("bulkDeleteArcheryAgeCategory", BulkDeleteArcheryAgeCategory::class);
        $this->registerService("getArcheryAgeCategory", GetArcheryAgeCategory::class);
        $this->registerService("addArcheryAgeCategory", AddArcheryAgeCategory::class);
        $this->registerService("deleteArcheryCategory", DeleteArcheryCategory::class);
        $this->registerService("bulkDeleteArcheryCategory", BulkDeleteArcheryCategory::class);
        $this->registerService("findArcheryCategory", FindArcheryCategory::class);
        $this->registerService("addArcheryCategory", AddArcheryCategory::class);
        $this->registerService("editArcheryCategory", EditArcheryCategory::class);
        $this->registerService("getArcheryCategory", GetArcheryCategory::class);
        $this->registerService("bulkDeleteArcheryClub", BulkDeleteArcheryClub::class);
        $this->registerService("findArcheryClub", FindArcheryClub::class);
        $this->registerService("deleteArcheryClub", DeleteArcheryClub::class);
        $this->registerService("editArcheryClub", EditArcheryClub::class);
        $this->registerService("addArcheryClub", AddArcheryClub::class);
        $this->registerService("getArcheryClub", GetArcheryClub::class);
        $this->registerService("editArcheryEvent", EditArcheryEvent::class);
        $this->registerService("deleteArcheryEvent", DeleteArcheryEvent::class);
        $this->registerService("getArcheryEvent", GetArcheryEvent::class);
        $this->registerService("addArcheryEvent", AddArcheryEvent::class);
        $this->registerService("findArcheryEvent", FindArcheryEvent::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
