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
use App\BLoC\Web\ArcheryEvent\UpdateArcheryEventStatus;
use App\BLoC\Web\ArcheryEvent\GetArcheryEventDetailById;
use App\BLoC\Web\ArcheryEvent\GetArcheryEventDetailBySlug;
use App\BLoC\Web\ArcheryEventCategories\GetArcheryEventCategory;
use App\BLoC\Web\ArcheryEventCategories\GetArcheryEventCategoryRegister;
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
use App\BLoC\General\CategoryDetail\GetListCategoryByEventId;
use App\BLoC\General\Event\GetDetailEventBySlugV2;
use App\BLoC\General\QandA\GetQandAByEventId;
use App\BLoC\Web\ArcheryScoring\AddParticipantMemberScore;
use App\BLoC\Web\ArcheryScoring\GetParticipantScore;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualification;
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
use App\BLoC\Web\ArcheryCategoryDetail\GetArcheryCategoryDetailQualification;
use App\BLoC\Web\ArcheryEventQualificationTime\AddArcheryEventQualificationTime;
use App\BLoC\Web\ArcheryCategoryDetail\AddArcheryCategoryDetail;
use App\BLoC\Web\ArcheryCategoryDetail\DeleteArcheryCategoryDetail;
use App\BLoC\Web\ArcheryCategoryDetail\EditArcheryCategoryDetail;
use App\BLoC\Web\ArcheryEvent\GetArcheryEventGlobal;
use App\BLoC\Web\ArcheryEventMasterTeamCategory\GetArcheryEventMasterTeamCategory;
use App\BLoC\Web\BudRest\SetBudRest;
use App\BLoC\Web\BudRest\GetBudRest;
use App\BLoC\Web\ArcheryEventMasterDistanceCategory\GetArcheryEventMasterDistanceCategory;
use App\BLoC\Web\ArcheryEventMasterCompetitionCategory\GetArcheryEventMasterCompetitionCategory;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\GetArcheryEventMasterAgeCategory;
use App\BLoC\Web\ArcheryEventQualificationTime\GetArcheryEventQualificationTime;
use App\BLoC\Web\ArcheryEventMoreInformation\EditArcheryEventMoreInformation;
use App\BLoC\Web\EventOrder\GetMemberParticipantIndividual;
use App\BLoC\Web\ArcheryEvent\EditArcheryEventSeparated;
use App\BLoC\Web\ArcheryCategoryDetail\EditArcheryEventCategoryDetailFee;
use App\BLoC\Web\ArcheryEventMoreInformation\DeleteArcheryEventMoreInformation;
use App\BLoC\Web\ArcheryEventMoreInformation\AddArcheryEventMoreInformation;
use App\BLoC\Web\ArcheryEvent\GetListArcheryEventDetail;
use App\BLoC\Web\AdminAuth\ValidateCodePassword;
use App\BLoC\Web\ArcheryCategoryDetail\CreateArcheryCategoryDetailV2;
use App\BLoC\Web\ArcheryCategoryDetail\CreateOrUpdateArcheryCategoryDetailV2;
use App\BLoC\Web\ArcheryCategoryDetail\DeleteCategoryDetailV2;
use App\BLoC\Web\ArcheryEvent\AddArcheryEventV2;
use App\BLoC\Web\ArcheryEvent\CreateArcheryEventV2;
use App\BLoC\Web\ArcheryEvent\DeleteHandBook;
use App\BLoC\Web\ArcheryEvent\UpdateArcheryEventV2;
use App\BLoC\Web\ArcheryEventIdcard\BulkDownloadCard;
use App\BLoC\Web\ArcheryEventParticipant\GetDownloadArcheryEventParticipant;
use App\BLoC\Web\ArcheryEventOfficial\GetDownloadArcheryEventOfficial;
use App\BLoC\Web\ArcheryScoreSheet\DownloadPdf;
use App\BLoC\Web\EliminationScoreSheet\DownloadEliminationScoreSheet;

use App\BLoC\Web\ArcheryUser\AcceptVerifyUser;
use App\BLoC\Web\UpdateParticipantByAdmin\UpdateParticipantCategory;
use App\BLoC\Web\Series\GetDownloadUserSeriePoint;
use App\BLoC\Web\UpdateParticipantByAdmin\Refund;
use App\BLoC\Web\ArcheryEventIdcard\AddUpdateArcheryEventIdCard;
use App\BLoC\Web\ArcheryEventQualificationTime\CreateQualificationTimeV2;
use App\BLoC\Web\Member\ListMemberV2;
use App\BLoC\Web\ArcheryReport\GetArcheryReportResult;
use App\BLoC\Web\BudRest\CreateOrUpdateBudRestV2;
use App\BLoC\Web\BudRest\GetBudRestV2;
use App\BLoC\Web\BudRest\GetListBudRestV2;
use App\BLoC\Web\Member\GetMemberAccessCategories;
use App\BLoC\Web\QandA\CreateQandA;
use App\BLoC\Web\QandA\DeleteQandA;
use App\BLoC\Web\Member\ListMemberTeamV2;
use App\BLoC\Web\ScheduleFullDay\GetScheduleFullDay;

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
        $this->registerService("getParticipantScoreQualification", GetParticipantScoreQualification::class);
        $this->registerService("getEventElimination", GetEventElimination::class);
        $this->registerService("setEventElimination", SetEventElimination::class);
        $this->registerService("setEventEliminationSchedule", SetEventEliminationSchedule::class);
        $this->registerService("removeEventEliminationSchedule", RemoveEventEliminationSchedule::class);
        $this->registerService("getEventEliminationTemplate", GetEventEliminationTemplate::class);
        $this->registerService("getEventEliminationSchedule", GetEventEliminationSchedule::class);
        $this->registerService("addArcheryEventCertificateTemplates", AddArcheryEventCertificateTemplates::class);
        $this->registerService("getArcheryEventCertificateTemplates", GetArcheryEventCertificateTemplates::class);
        $this->registerService("getArcheryCategoryDetail", GetArcheryCategoryDetail::class);
        $this->registerService("getArcheryCategoryDetailQualification", GetArcheryCategoryDetailQualification::class);
        $this->registerService("addArcheryEventQualificationTime", AddArcheryEventQualificationTime::class);
        $this->registerService("addArcheryCategoryDetail", AddArcheryCategoryDetail::class);
        $this->registerService("deleteArcheryCategoryDetail", DeleteArcheryCategoryDetail::class);
        $this->registerService("editArcheryCategoryDetail", EditArcheryCategoryDetail::class);
        $this->registerService("getArcheryScoring", GetArcheryScoring::class);
        $this->registerService("getArcheryEventGlobal", GetArcheryEventGlobal::class);
        $this->registerService("getArcheryEventMasterTeamCategory", GetArcheryEventMasterTeamCategory::class);
        $this->registerService("setBudRest", SetBudRest::class);
        $this->registerService("getBudRest", GetBudRest::class);
        $this->registerService("getArcheryEventMasterDistanceCategory", GetArcheryEventMasterDistanceCategory::class);
        $this->registerService("getArcheryEventMasterCompetitionCategory", GetArcheryEventMasterCompetitionCategory::class);
        $this->registerService("getArcheryEventMasterAgeCategory", GetArcheryEventMasterAgeCategory::class);
        $this->registerService("updateArcheryEventStatus", UpdateArcheryEventStatus::class);
        $this->registerService("getArcheryEventDetailById", GetArcheryEventDetailById::class);
        $this->registerService("getArcheryEventQualificationTime", GetArcheryEventQualificationTime::class);
        $this->registerService("editArcheryEventMoreInformation", EditArcheryEventMoreInformation::class);
        $this->registerService("editArcheryEventSeparated", EditArcheryEventSeparated::class);
        $this->registerService("editArcheryEventCategoryDetailFee", EditArcheryEventCategoryDetailFee::class);
        $this->registerService("getArcheryEventDetailBySlug", GetArcheryEventDetailBySlug::class);
        $this->registerService("getArcheryEventCategoryRegister", GetArcheryEventCategoryRegister::class);
        $this->registerService("getMemberParticipantIndividual", GetMemberParticipantIndividual::class);
        $this->registerService("deleteArcheryEventMoreInformation", DeleteArcheryEventMoreInformation::class);
        $this->registerService("addArcheryEventMoreInformation", AddArcheryEventMoreInformation::class);
        $this->registerService("getListArcheryEventDetail", GetListArcheryEventDetail::class);
        $this->registerService("validateCodePassword", ValidateCodePassword::class);
        $this->registerService("bulkDownloadCard", BulkDownloadCard::class);
        $this->registerService("getDownloadArcheryEventParticipant", GetDownloadArcheryEventParticipant::class);
        $this->registerService("acceptVerifyUser", AcceptVerifyUser::class);
        $this->registerService("getDownloadArcheryEventOfficial", GetDownloadArcheryEventOfficial::class);

        $this->registerService("downloadPdf", DownloadPdf::class);

        $this->registerService("downloadEliminationScoreSheet", DownloadEliminationScoreSheet::class);

        $this->registerService("updateParticipantCategory", UpdateParticipantCategory::class);
        $this->registerService("getDownloadUserSeriePoint", GetDownloadUserSeriePoint::class);
        $this->registerService("refund", Refund::class);
        $this->registerService("addUpdateArcheryEventIdCard", AddUpdateArcheryEventIdCard::class);
        $this->registerService("deleteHandBook", DeleteHandBook::class);
        $this->registerService("getArcheryReportResult", GetArcheryReportResult::class);

        // Api v2
        // ========================== event =================================
        $this->registerService("createArcheryEventV2", CreateArcheryEventV2::class);
        $this->registerService("updateArcheryEventV2", updateArcheryEventV2::class);

        // =========================== Category =============================
        $this->registerService("createOrUpdateArcheryCategoryDetailV2", CreateOrUpdateArcheryCategoryDetailV2::class);
        $this->registerService("deleteCategoryDetailV2", DeleteCategoryDetailV2::class);

        // =========================== Member ==============================
        $this->registerService("listMemberV2", ListMemberV2::class);
        $this->registerService("getMemberAccessCategories", GetMemberAccessCategories::class);
        $this->registerService("listMemberTeamV2", ListMemberTeamV2::class);

        // =========================== Qualification-time ===================================
        $this->registerService("createQualificationTimeV2", CreateQualificationTimeV2::class);

        // =============================== Q and A ============================================
        $this->registerService("createQandA", CreateQandA::class);
        $this->registerService("deleteQandA", DeleteQandA::class);
        $this->registerService("getQandAByEventId", GetQandAByEventId::class);

        // ================================ category details ===================================
        $this->registerService("getListCategoryByEventId", GetListCategoryByEventId::class);

        // ================================= Events ==========================================
        $this->registerService("getDetailEventBySlugV2", GetDetailEventBySlugV2::class);

        // ================================== Bud Rest =======================================
        $this->registerService("getBudRestV2", GetBudRestV2::class);
        $this->registerService("createOrUpdateBudRestV2", CreateOrUpdateBudRestV2::class);
        $this->registerService("getListBudRestV2", GetListBudRestV2::class);

        // ================================== Schedule full day ================================
        $this->registerService("getScheduleFullDay", GetScheduleFullDay::class);
    }


    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
