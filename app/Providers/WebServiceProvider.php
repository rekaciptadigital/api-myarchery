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
use App\BLoC\Web\ArcheryEvent\EditArcheryEvent;
use App\BLoC\Web\ArcheryEvent\DeleteArcheryEvent;
use App\BLoC\Web\ArcheryEvent\GetArcheryEvent;
use App\BLoC\Web\ArcheryEvent\AddArcheryEvent;
use App\BLoC\Web\ArcheryEvent\FindArcheryEvent;
use App\BLoC\Web\ArcheryEvent\FindArcheryEventBySlug;
use App\BLoC\Web\ArcheryEventCategories\GetArcheryEventCategory;
use App\BLoC\Web\ArcheryEventParticipant\EditArcheryEventParticipantScore;
use App\BLoC\Web\ArcheryEventParticipant\GetArcheryEventParticipantMemberProfile;
use App\BLoC\Web\ArcheryEventParticipant\GetArcheryEventParticipant;
use App\BLoC\Web\ArcheryEventParticipant\GetArcheryEventParticipantMember;
use App\BLoC\Web\ArcheryEventParticipant\GetArcheryEventParticipantScore;
use App\BLoC\Web\ArcheryScoring\FindParticipantScoreBySchedule;
use App\BLoC\Web\EventOrder\AddEventOrder;
use App\BLoC\Web\EventOrder\DetailEventOrder;
use App\BLoC\Web\EventOrder\GetEventOrder;
use App\BLoC\Web\EventOrder\GetEventPrice;
use App\BLoC\Web\Transaction\CallbackMidtrans;
use App\BLoC\Web\EventQualificationScheduleByEo\GetEventQualificationScheduleByEo;
use App\BLoC\Web\EventQualificationScheduleByEo\GetEventMemberQualificationScheduleByEo;
use App\BLoC\App\EventQualificationSchedule\GetEventQualificationSchedule;
use App\BLoC\App\EventQualificationSchedule\SetEventQualificationSchedule;
use App\BLoC\App\EventQualificationSchedule\UnsetEventQualificationSchedule;
use App\BLoC\Web\ArcheryScoring\AddParticipantMemberScore;
use App\BLoC\Web\ArcheryScoring\GetParticipantScore;
use App\BLoC\Web\ArcheryScoring\GetArcheryScoring;
use App\BLoC\Web\EventElimination\GetEventEliminationTemplate;
use App\BLoC\Web\EventElimination\GetEventElimination;
use App\BLoC\Web\EventElimination\SetEventElimination;
use App\BLoC\Web\EventElimination\SetEventEliminationSchedule;
use App\BLoC\Web\EventElimination\RemoveEventEliminationSchedule;
use App\BLoC\Web\EventElimination\GetEventEliminationSchedule;
use App\BLoC\Web\ArcheryEventCertificateTemplates\AddArcheryEventCertificateTemplates;
use App\BLoC\Web\ArcheryEventCertificateTemplates\GetArcheryEventCertificateTemplates;
use App\BLoC\Web\ArcheryCategoryDetail\GetArcheryCategoryDetail;
use App\BLoC\Web\ArcheryEventQualificationTime\AddArcheryEventQualificationTime;
use App\BLoC\Web\ArcheryCategoryDetail\AddArcheryCategoryDetail;
use App\BLoC\Web\ArcheryCategoryDetail\DeleteArcheryCategoryDetail;
use App\BLoC\Web\ArcheryCategoryDetail\EditArcheryCategoryDetail;

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
        $this->registerService("editArcheryEvent", EditArcheryEvent::class);
        $this->registerService("deleteArcheryEvent", DeleteArcheryEvent::class);
        $this->registerService("getArcheryEvent", GetArcheryEvent::class);
        $this->registerService("addArcheryEvent", AddArcheryEvent::class);
        $this->registerService("findArcheryEvent", FindArcheryEvent::class);
        $this->registerService("findArcheryEventBySlug", FindArcheryEventBySlug::class);
        $this->registerService("getArcheryEventCategory", GetArcheryEventCategory::class);
        $this->registerService("editArcheryEventParticipantScore", EditArcheryEventParticipantScore::class);
        $this->registerService("getArcheryEventParticipant", GetArcheryEventParticipant::class);
        $this->registerService("getArcheryEventParticipantScore", GetArcheryEventParticipantScore::class);
        $this->registerService("findParticipantScoreBySchedule", FindParticipantScoreBySchedule::class);
        $this->registerService("getArcheryEventScoringSytem", GetArcheryEventScoringSytem::class);
        $this->registerService("addEventOrder", AddEventOrder::class);
        $this->registerService("detailEventOrder", DetailEventOrder::class);
        $this->registerService("getEventOrder", GetEventOrder::class);
        $this->registerService("callbackMidtrans", CallbackMidtrans::class);
        $this->registerService("getEventPrice", GetEventPrice::class);
        $this->registerService("getEventQualificationSchedule", GetEventQualificationSchedule::class);
        $this->registerService("setEventQualificationSchedule", SetEventQualificationSchedule::class);
        $this->registerService("unsetEventQualificationSchedule", UnsetEventQualificationSchedule::class);
        $this->registerService("getEventQualificationScheduleByEo", GetEventQualificationScheduleByEo::class);
        $this->registerService("getEventMemberQualificationScheduleByEo", GetEventMemberQualificationScheduleByEo::class);
        $this->registerService("getArcheryEventParticipantMemberProfile", GetArcheryEventParticipantMemberProfile::class);
        $this->registerService("getArcheryEventParticipantMember", GetArcheryEventParticipantMember::class);
        $this->registerService("addParticipantMemberScore", AddParticipantMemberScore::class);
        $this->registerService("getParticipantScore", GetParticipantScore::class);
        $this->registerService("getEventElimination", GetEventElimination::class);
        $this->registerService("setEventElimination", SetEventElimination::class);
        $this->registerService("setEventEliminationSchedule", SetEventEliminationSchedule::class);
        $this->registerService("removeEventEliminationSchedule", RemoveEventEliminationSchedule::class);
        $this->registerService("getEventEliminationTemplate", GetEventEliminationTemplate::class);
        $this->registerService("getEventEliminationSchedule", GetEventEliminationSchedule::class);
        $this->registerService("addArcheryEventCertificateTemplates", AddArcheryEventCertificateTemplates::class);
        $this->registerService("getArcheryEventCertificateTemplates", GetArcheryEventCertificateTemplates::class);
        $this->registerService("getArcheryCategoryDetail", GetArcheryCategoryDetail::class);
        $this->registerService("addArcheryEventQualificationTime", AddArcheryEventQualificationTime::class);
        $this->registerService("addArcheryCategoryDetail", AddArcheryCategoryDetail::class);
        $this->registerService("deleteArcheryCategoryDetail", DeleteArcheryCategoryDetail::class);
        $this->registerService("editArcheryCategoryDetail", EditArcheryCategoryDetail::class);
        $this->registerService("getArcheryScoring", GetArcheryScoring::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
