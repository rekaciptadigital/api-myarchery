<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\BLoC\App\ArcheryEventParticipant\FindParticipantDetail;
use App\BLoC\App\ArcheryEventParticipant\EditParticipantProfile;
use Illuminate\Support\ServiceProvider;

use App\BLoC\App\UserAuth\UserForgotPassword;
use App\BLoC\App\UserAuth\UserLogin;
use App\BLoC\App\UserAuth\UserRegister;
use App\BLoC\App\UserAuth\UserResetPassword;
use App\BLoC\App\UserAuth\UserValidateCodePassword;
use App\BLoC\App\UserAuth\GetUserProfile;
use App\BLoC\App\UserAuth\UpdateVerifikasiUser;
use App\BLoC\App\UserAuth\GetDataUserVerifikasi;
use App\BLoC\App\UserAuth\UserLogout;
use App\BLoC\App\Certificate\GetListDownloadCertificate;
use App\BLoC\App\Certificate\GetDownload;
use App\BLoC\App\ArcheryClub\CreateArcheryClub;
use App\BLoC\App\ArcheryClub\JoinArcheryClub;
use App\BLoC\App\ArcheryClub\LeftArcheryClub;
use App\BLoC\App\ArcheryClub\KickMember;
use App\BLoC\App\ArcheryClub\GetArcheryClubs;
use App\BLoC\App\ArcheryClub\UpdateArcheryClub;
use App\BLoC\App\ArcheryClub\GetProfileClub;
use App\BLoC\App\ArcheryClub\GetMyClub;
use App\BLoC\App\ArcheryClub\GetAllMemberByClubId;
use App\BLoC\App\ArcheryEvent\GetDetailEventById;
use App\BLoC\App\ArcheryEvent\GetEventCategoryDetail;
use App\BLoC\App\ArcheryEvent\GetListCategoryByUserLogin;
use App\BLoC\App\ArcheryEvent\GetListEventByUserLogin;
use App\BLoC\App\ArcheryEvent\GetParticipantMemberByCategory;
use App\BLoC\App\ArcheryEvent\UpdateParticipantMember;
use App\BLoC\App\UserAuth\UpdateUserProfile;
use App\BLoC\App\UserAuth\UpdateUserAvatar;
use App\BLoC\General\GetProvince;
use App\BLoC\General\GetCity;
use App\BLoC\App\ArcheryEventIdcard\GetDownloadCard;
use App\BLoC\App\ArcheryEventOfficial\AddOrderOfficial;
use App\BLoC\App\ArcheryEventOfficial\GetDetailOrderOfficial;
use App\BLoC\App\ArcheryEventOfficial\GetEventOfficialDetail;
use App\BLoC\App\ArcheryEventOfficial\GetOrderEventOfficial;
use App\BLoC\App\Series\GetDetailSeriesById;
use App\BLoC\App\Series\GetListCategorySeries;
use App\BLoC\App\Series\GetListEventBySeriesId;
use App\BLoC\App\Series\GetListParticipantByCategorySeriesId;
use App\BLoC\App\Series\GetListSeries;
use App\BLoC\App\Series\SetMemberSeries;
use App\BLoC\General\GetListOfficial;
use App\BLoC\App\EventOrder\GetEventOrderV2;
use App\BLoC\App\UserAuth\ResendOtpAccountVerificationCode;
use App\BLoC\App\UserAuth\ValidateAccoutVerification;

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
        $this->registerService("userValidateCodePassword", UserValidateCodePassword::class);
        $this->registerService("getUserProfile", GetUserProfile::class);
        $this->registerService("userLogout", UserLogout::class);
        // fast open 3 
        $this->registerService("resendOtpAccountVerificationCode", ResendOtpAccountVerificationCode::class);
        $this->registerService("validateAccoutVerification", ValidateAccoutVerification::class);
        // end fast open 3
        $this->registerService("findParticipantDetail", FindParticipantDetail::class);
        $this->registerService("addParticipantScore", AddParticipantScore::class);
        $this->registerService("editParticipantProfile", EditParticipantProfile::class);
        $this->registerService("getParticipantScoreSummary", GetParticipantScoreSummary::class);
        $this->registerService("getListDownloadCertificate", GetListDownloadCertificate::class);
        $this->registerService("getDownload", GetDownload::class);
        $this->registerService('createArcheryClub', CreateArcheryClub::class);
        $this->registerService('joinArcheryClub', JoinArcheryClub::class);
        $this->registerService('leftArcheryClub', LeftArcheryClub::class);
        $this->registerService('kickMember', KickMember::class);
        $this->registerService('getArcheryClubs', GetArcheryClubs::class);
        $this->registerService('updateArcheryClub', UpdateArcheryClub::class);
        $this->registerService('getProfileClub', GetProfileClub::class);
        $this->registerService('getMyClub', GetMyClub::class);
        $this->registerService('getProvince', GetProvince::class);
        $this->registerService('getCity', GetCity::class);
        $this->registerService('getAllMemberByClubId', GetAllMemberByClubId::class);

        $this->registerService('userUpdateProfile', UpdateUserProfile::class);
        $this->registerService('userUpdateAvatar', UpdateUserAvatar::class);
        $this->registerService("getDownloadCard", GetDownloadCard::class);

        $this->registerService("getListEventByUserLogin", GetListEventByUserLogin::class);
        $this->registerService("getListCategoryByUserLogin", GetListCategoryByUserLogin::class);
        $this->registerService("getEventCategoryDetail", GetEventCategoryDetail::class);
        $this->registerService("getParticipantMemberByCategory", GetParticipantMemberByCategory::class);
        $this->registerService("updateParticipantMember", UpdateParticipantMember::class);
        $this->registerService("getDetailEventById", GetDetailEventById::class);


        $this->registerService("updateVerifikasiUser", UpdateVerifikasiUser::class);
        $this->registerService("getDataUserVerifikasi", GetDataUserVerifikasi::class);

        $this->registerService("addOrderOfficial", AddOrderOfficial::class);
        $this->registerService("getDetailOrderOfficial", GetDetailOrderOfficial::class);
        $this->registerService("getOrderEventOfficial", GetOrderEventOfficial::class);
        $this->registerService("getEventOfficialDetail", GetEventOfficialDetail::class);
        $this->registerService("getListOfficial", GetListOfficial::class);

        $this->registerService("setMemberSeries", SetMemberSeries::class);
        $this->registerService("getListSeries", GetListSeries::class);
        $this->registerService("getListEventBySeriesId", GetListEventBySeriesId::class);
        $this->registerService("getListCategorySeries", GetListCategorySeries::class);
        $this->registerService("getListParticipantByCategorySeriesId", GetListParticipantByCategorySeriesId::class);
        $this->registerService("getDetailSeriesById", GetDetailSeriesById::class);
        $this->registerService("getEventOrderV2", getEventOrderV2::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
    public function boot()
    {
        Schema::defaultStringLength(191);
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
    }
}
