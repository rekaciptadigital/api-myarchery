<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\BLoC\Web\AdminAuth\ForgotPassword;
use App\BLoC\Web\AdminAuth\Login;
use App\BLoC\Web\AdminAuth\Register;
use App\BLoC\Web\AdminAuth\CheckAdminRegister;
use App\BLoC\Web\AdminAuth\ResetPassword;
use App\BLoC\Web\AdminAuth\GetProfile;
use App\BLoC\Web\AdminAuth\Logout;
use App\BLoC\Web\AdminAuth\Password;
use App\BLoC\Web\AdminAuth\UpdateAdminProfile;
use App\BLoC\Web\AdminAuth\UpdateAdminAvatar;
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
use App\BLoC\General\Event\GetDetailEventByIdGeneral;
use App\BLoC\General\Event\GetDetailEventBySlugV2;
use App\BLoC\General\GetEventClubRanked;
use App\BLoC\General\QandA\GetQandAByEventId;
use App\BLoC\Web\AdminAuth\UpdateProfile;
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
use App\BLoC\Web\BudRest\GetIdCardByCategory;
use App\BLoC\Web\BudRest\GetIdCardByBudrest;
use App\BLoC\Web\BudRest\GetIdCardByClub;
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
use App\BLoC\Web\ArcheryCategoryDetail\CreateOrUpdateArcheryCategoryDetailV2;
use App\BLoC\Web\ArcheryCategoryDetail\DeleteCategoryDetailV2;
use App\BLoC\Web\ArcheryCategoryDetail\GetConfigCategoryRegistrationDate;
use App\BLoC\Web\ArcheryCategoryDetail\SetConfigRegisterCategory;
use App\BLoC\Web\ArcheryEvent\AddLogoEvent;
use App\BLoC\Web\ArcheryEvent\CreateArcheryEventV2;
use App\BLoC\Web\ArcheryEvent\DeleteEvent;
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
use App\BLoC\Web\ArcheryEventIdcard\BulkDownloadIdCardByCategoryIdV2;
use App\BLoC\Web\ArcheryEventIdcard\CreateOrUpdateIdCardTemplateV2;
use App\BLoC\Web\ArcheryEventIdcard\FindIdCardByMmeberOrOfficialId;
use App\BLoC\Web\ArcheryEventIdcard\GetTemplateIdCardByEventIdV2;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\CreateMasterAgeCategoryByAdmin;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\GetArcheryMasterAgeCategoryByAdmin;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\GetDetailMasterAgeCategory;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\UpdateIsHideAgeCategory;
use App\BLoC\Web\ArcheryEventMasterAgeCategory\UpdateMasterAgeCategoryByAdmin;
use App\BLoC\Web\ArcheryEventQualificationTime\CreateQualificationTimeV2;
use App\BLoC\Web\Member\ListMemberV2;
use App\BLoC\Web\ArcheryReport\GetArcheryReportResultV2;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreQualificationV2;
use App\BLoC\Web\BudRest\CreateOrUpdateBudRestV2;
use App\BLoC\Web\BudRest\GetBudRestV2;
use App\BLoC\Web\BudRest\GetListBudRestV2;
use App\BLoC\Web\Member\GetMemberAccessCategories;
use App\BLoC\Web\QandA\CreateQandA;
use App\BLoC\Web\QandA\DeleteQandA;
use App\BLoC\Web\QandA\GetQandADetail;
use App\BLoC\Web\QandA\EditQandA;
use App\BLoC\Web\Member\ListMemberTeamV2;
use App\BLoC\Web\ScheduleFullDay\GetScheduleFullDay;
use App\BLoC\Web\ScheduleFullDay\UpdateMemberBudrest;
use App\BLoC\Web\ArcheryEventOfficial\AddArcheryEventOfficialDetail;
use App\BLoC\Web\ArcheryEventOfficial\GetAllArcheryEventOfficial;
use App\BLoC\Web\ArcheryEventOfficial\EditArcheryEventOfficialDetail;
use App\BLoC\Web\ArcheryEventOfficial\GetArcheryEventOfficialDetail;
use App\BLoC\Web\ArcheryScoring\SetAdminTotal;
use App\BLoC\Web\ArcheryScoring\SetSavePermanentElimination;
use App\BLoC\Web\EventElimination\SetBudRestElimination;
use App\BLoC\Web\EventElimination\SetEventEliminationCountParticipant;
use App\BLoC\Web\EventElimination\SetEventEliminationV2;
use App\BLoC\Web\UpdateParticipantByAdmin\ChangeIsPresent;
use App\BLoC\Web\UpdateParticipantByAdmin\InsertParticipantByAdmin;
use App\BLoC\Web\DashboardDos\GetArcheryEventScheduleDashboardDos;
use App\BLoC\Web\DashboardDos\DownloadScoreQualification;
use App\BLoC\Web\DashboardDos\DownloadEliminationDashboardDos;
use App\BLoC\Web\EventElimination\CleanEliminationMatch;
use App\BLoC\Web\DashboardDos\GetParticipantScoreQualificationDos;
use App\BLoC\Web\EventElimination\CleanScoringQualification;
use App\BLoC\Web\ScheduleFullDay\DownloadMemberBudrest;
use App\BLoC\Web\ArcheryReport\GetArcheryReportEventList;
use App\BLoC\Web\ArcheryReport\GetArcheryReportClubRanked;
use App\BLoC\Web\ArcheryReport\ReportMedalClub;
use App\BLoC\Web\ArcheryReport\Upp;
use App\BLoC\Web\Member\BulkInsertUserParticipant;
use App\BLoC\Web\ArcheryScoring\ResetScoringEliminasi;
use App\BLoC\Web\ClubRanked\GetConfigClubRanked;
use App\BLoC\Web\ClubRanked\SetConfigClubRanked;
use App\BLoC\Web\EliminationScoreSheet\BulkDownloadScooresSheetElimination;
use App\BLoC\Web\EliminationScoreSheet\DownloadEmptyScoreSheetElimination;
use App\BLoC\Web\EventOrder\BookingTemporary;
use App\BLoC\Web\EventOrder\DeleteBookingTemporary;
use App\BLoC\Web\UpdateParticipantByAdmin\ImportParticipantExcell;
use App\BLoC\Web\ArcheryScoreSheet\DownloadQualificationSelectionScoresheet;
use App\BLoC\Web\ArcheryScoreSheet\DownloadEliminationSelectionScoresheet;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreEliminationSelection;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreEventSelection;
use App\BLoC\Web\ArcheryReport\GetArcheryReportEventSelection;

// Archery Enterprise Section //
use App\BLoC\Web\Enterprise\Venue\CreateVenuePlace;
use App\BLoC\Web\Enterprise\Venue\GetVenuePlace;
use App\BLoC\Web\Enterprise\Venue\GetVenueMasterPlaceFacilities;
use App\BLoC\Web\Enterprise\Venue\GetListVenuePlace;
use App\BLoC\Web\Enterprise\Venue\GetVenuePlaceOtherFacilitiesByEoId;
use App\BLoC\Web\Enterprise\Venue\UpdateIsHideOtherFacilities;
use App\BLoC\Web\Enterprise\Venue\DeleteImageVenuePlace;
use App\BLoC\Web\Enterprise\Venue\UpdateVenuePlace;
use App\BLoC\Web\Enterprise\Venue\DeleteDraftVenuePlace;
use App\BLoC\Web\Enterprise\Venue\ScheduleOperational\AddVenueScheduleOperational;
use App\BLoC\Web\Enterprise\Venue\ScheduleOperational\GetVenueScheduleOperationalDetailById;
use App\BLoC\Web\Enterprise\Venue\ScheduleOperational\GetListVenueScheduleOperationalByPlaceId;
use App\BLoC\Web\Enterprise\Venue\ScheduleOperational\UpdateVenueScheduleOperational;
use App\BLoC\Web\Enterprise\Venue\ScheduleHoliday\AddVenueScheduleHoliday;
use App\BLoC\Web\Enterprise\Venue\ScheduleHoliday\GetVenueScheduleHolidayDetailById;
use App\BLoC\Web\Enterprise\Venue\ScheduleHoliday\GetListVenueScheduleHolidayByPlaceId;
use App\BLoC\Web\Enterprise\Venue\ScheduleHoliday\UpdateVenueScheduleHoliday;
use App\BLoC\Web\Enterprise\Venue\ScheduleHoliday\DeleteVenueScheduleHoliday;
use App\BLoC\Web\Enterprise\Venue\GetVenueMasterPlaceCapacityArea;
use App\BLoC\Web\Enterprise\Venue\CompleteVenuePlace;
use App\BLoC\Web\EventElimination\ChangeMemberJoinEliminationGroup;
use App\BLoC\Web\EventElimination\GetMemberCanJoinEliminationGroup;
use App\BLoC\Web\ManagementAdmin\CheckAdminExists;
use App\BLoC\Web\ManagementAdmin\CreateNewUser;
use App\BLoC\Web\ManagementAdmin\GetDetailAdmin;
use App\BLoC\Web\ManagementAdmin\GetListAdmin;
use App\BLoC\Web\ManagementAdmin\GetListRole;
use App\BLoC\Web\ManagementAdmin\RemoveAccessAdmin;

// End of Archery Enterprise Section //

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
        $this->registerService("checkAdminRegister", CheckAdminRegister::class);
        $this->registerService("resetPassword", ResetPassword::class);
        $this->registerService("updateProfile", UpdateProfile::class);
        $this->registerService("getProfile", GetProfile::class);
        $this->registerService("logout", Logout::class);
        $this->registerService("password", Password::class);
        $this->registerService("updateAdminProfile", UpdateAdminProfile::class);
        $this->registerService("updateAdminAvatar", UpdateAdminAvatar::class);
        $this->registerService("deleteArcheryCategory", DeleteArcheryCategory::class);
        $this->registerService("bulkDeleteArcheryCategory", BulkDeleteArcheryCategory::class);
        $this->registerService("findArcheryCategory", FindArcheryCategory::class);
        $this->registerService("addArcheryCategory", AddArcheryCategory::class);
        $this->registerService("editArcheryCategory", EditArcheryCategory::class);
        $this->registerService("getArcheryCategory", GetArcheryCategory::class);

        // ============================= Event ==============================
        $this->registerService("editArcheryEvent", EditArcheryEvent::class);
        $this->registerService("deleteArcheryEvent", DeleteArcheryEvent::class);
        $this->registerService("getArcheryEvent", GetArcheryEvent::class);
        $this->registerService("addArcheryEvent", AddArcheryEvent::class);
        $this->registerService("findArcheryEvent", FindArcheryEvent::class);
        $this->registerService("findArcheryEventBySlug", FindArcheryEventBySlug::class);

        // ========================= Fast Open ========================
        $this->registerService("addLogoEvent", AddLogoEvent::class);
        $this->registerService("getMemberCanJoinEliminationGroup", GetMemberCanJoinEliminationGroup::class);
        $this->registerService("changeMemberJoinEliminationGroup", ChangeMemberJoinEliminationGroup::class);
        $this->registerService("downloadEmptyScoreSheetElimination", DownloadEmptyScoreSheetElimination::class);
        $this->registerService("setConfigRegisterCategory", SetConfigRegisterCategory::class);
        $this->registerService("getConfigCategoryRegistrationDate", GetConfigCategoryRegistrationDate::class);
        
        $this->registerService("createNewUser", CreateNewUser::class);
        $this->registerService("checkAdminExists", CheckAdminExists::class);
        $this->registerService("getListRole", GetListRole::class);
        $this->registerService("getListAdmin", GetListAdmin::class);
        $this->registerService("getDetailAdmin", GetDetailAdmin::class);
        $this->registerService("removeAccessAdmin", RemoveAccessAdmin::class);
        $this->registerService("deleteEvent", DeleteEvent::class);
        // =========================== End ============================

        $this->registerService("getArcheryEventCategory", GetArcheryEventCategory::class);
        $this->registerService("editArcheryEventParticipantScore", EditArcheryEventParticipantScore::class);
        $this->registerService("getArcheryEventParticipant", GetArcheryEventParticipant::class);
        $this->registerService("getArcheryEventParticipantScore", GetArcheryEventParticipantScore::class);
        $this->registerService("findParticipantScoreBySchedule", FindParticipantScoreBySchedule::class);
        $this->registerService("getArcheryEventScoringSytem", GetArcheryEventScoringSytem::class);

        $this->registerService("addEventOrder", AddEventOrder::class);
        $this->registerService("detailEventOrder", DetailEventOrder::class);
        $this->registerService("bookingTemporary", BookingTemporary::class);
        $this->registerService("deleteBookingTemporary", DeleteBookingTemporary::class);

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


        // ==================================== master age category ======================================
        $this->registerService("getArcheryEventMasterAgeCategory", GetArcheryEventMasterAgeCategory::class);
        $this->registerService("getArcheryMasterAgeCategoryByAdmin", GetArcheryMasterAgeCategoryByAdmin::class);
        $this->registerService("createMasterAgeCategoryByAdmin", CreateMasterAgeCategoryByAdmin::class);
        $this->registerService("updateMasterAgeCategoryByAdmin", UpdateMasterAgeCategoryByAdmin::class);
        $this->registerService("getDetailMasterAgeCategory", GetDetailMasterAgeCategory::class);
        $this->registerService("updateIsHideAgeCategory", UpdateIsHideAgeCategory::class);


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
        $this->registerService("reportMedalClub", ReportMedalClub::class);
        $this->registerService("upp", Upp::class);

        $this->registerService("downloadEliminationScoreSheet", DownloadEliminationScoreSheet::class);
        $this->registerService("downloadQualificationSelectionScoresheet", DownloadQualificationSelectionScoresheet::class);
        $this->registerService("downloadEliminationSelectionScoresheet", DownloadEliminationSelectionScoresheet::class);

        $this->registerService("updateParticipantCategory", UpdateParticipantCategory::class);
        $this->registerService("getDownloadUserSeriePoint", GetDownloadUserSeriePoint::class);
        $this->registerService("refund", Refund::class);
        $this->registerService("addUpdateArcheryEventIdCard", AddUpdateArcheryEventIdCard::class);
        $this->registerService("deleteHandBook", DeleteHandBook::class);
        $this->registerService("getArcheryReportResult", GetArcheryReportResultV2::class);
        $this->registerService("reportMedalClub", ReportMedalClub::class);
        $this->registerService("getArcheryReportEventList", GetArcheryReportEventList::class);
        $this->registerService("downloadMemberBudrest", DownloadMemberBudrest::class);

        $this->registerService("getEventClubRanked", GetEventClubRanked::class);
        $this->registerService("getArcheryReportClubRanked", GetArcheryReportClubRanked::class);

        // ============================ Api v2 =======================================
        // ========================== event =================================
        $this->registerService("createArcheryEventV2", CreateArcheryEventV2::class);
        $this->registerService("updateArcheryEventV2", updateArcheryEventV2::class);
        $this->registerService("getArcheryReportEventSelection", GetArcheryReportEventSelection::class);

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
        $this->registerService("getQandADetail", GetQandADetail::class);
        $this->registerService("editQandA", EditQandA::class);

        // ================================ category details ===================================
        $this->registerService("getListCategoryByEventId", GetListCategoryByEventId::class);

        // ================================= Events ==========================================
        $this->registerService("getDetailEventBySlugV2", GetDetailEventBySlugV2::class);
        $this->registerService("getDetailEventByIdGeneral", GetDetailEventByIdGeneral::class);

        // ================================== Bud Rest =======================================
        $this->registerService("getBudRestV2", GetBudRestV2::class);
        $this->registerService("createOrUpdateBudRestV2", CreateOrUpdateBudRestV2::class);
        $this->registerService("getListBudRestV2", GetListBudRestV2::class);
        $this->registerService("getIdCardByCategory", GetIdCardByCategory::class);
        $this->registerService("getIdCardByBudrest", GetIdCardByBudrest::class);
        $this->registerService("getIdCardByClub", GetIdCardByClub::class);

        // ================================== Schedule full day ================================
        $this->registerService("getScheduleFullDay", GetScheduleFullDay::class);
        $this->registerService("updateMemberBudrest", UpdateMemberBudrest::class);

        // ================================== Scorer Qualification ===============================
        $this->registerService("getParticipantScoreQualificationV2", GetParticipantScoreQualificationV2::class);


        // ================================== Official v2 ================================
        $this->registerService("addArcheryEventOfficialDetail", AddArcheryEventOfficialDetail::class);
        $this->registerService("getAllArcheryEventOfficial", GetAllArcheryEventOfficial::class);
        $this->registerService("editArcheryEventOfficialDetail", EditArcheryEventOfficialDetail::class);
        $this->registerService("getArcheryEventOfficialDetail", GetArcheryEventOfficialDetail::class);

        // ================================== id card ============================================
        $this->registerService("createOrUpdateIdCardTemplateV2", CreateOrUpdateIdCardTemplateV2::class);
        $this->registerService("getTemplateIdCardByEventIdV2", GetTemplateIdCardByEventIdV2::class);
        $this->registerService("bulkDownloadIdCardByCategoryIdV2", BulkDownloadIdCardByCategoryIdV2::class);
        $this->registerService("findIdCardByMmeberOrOfficialId", FindIdCardByMmeberOrOfficialId::class);

        // ================================== participant v2 ==============================
        $this->registerService("changeIsPresent", ChangeIsPresent::class);
        $this->registerService("insertParticipantByAdmin", InsertParticipantByAdmin::class);


        // ================================== event-elimination v2 ==========================
        $this->registerService("setEventEliminationV2", SetEventEliminationV2::class);
        $this->registerService("setEventEliminationCountParticipant", SetEventEliminationCountParticipant::class);
        $this->registerService("setBudRestElimination", SetBudRestElimination::class);
        $this->registerService("cleanEliminationMatch", CleanEliminationMatch::class);
        $this->registerService("cleanScoringQualification", CleanScoringQualification::class);
        $this->registerService("getParticipantScoreEliminationSelection", GetParticipantScoreEliminationSelection::class);

        // ================================ scorer-elimination v2 ==================================
        $this->registerService("setAdminTotal", SetAdminTotal::class);
        $this->registerService("setSavePermanentElimination", SetSavePermanentElimination::class);
        $this->registerService("resetScoringEliminasi", ResetScoringEliminasi::class);
        $this->registerService("getParticipantScoreEventSelection", GetParticipantScoreEventSelection::class);

        // ================================ dashboard dos ==================================
        $this->registerService("getArcheryEventScheduleDashboardDos", GetArcheryEventScheduleDashboardDos::class);
        $this->registerService("downloadScoreQualification", DownloadScoreQualification::class);
        $this->registerService("downloadEliminationDashboardDos", DownloadEliminationDashboardDos::class);
        $this->registerService("getParticipantScoreQualificationDos", GetParticipantScoreQualificationDos::class);



        // ======================================== Fats Open 3 ==========================================
        $this->registerService("setConfigClubRanked", SetConfigClubRanked::class);
        $this->registerService("getConfigClubRanked", GetConfigClubRanked::class);
        $this->registerService("bulkInsertUserParticipant", BulkInsertUserParticipant::class);
        $this->registerService("importParticipantExcell", ImportParticipantExcell::class);
        $this->registerService("bulkDownloadScooresSheetElimination", BulkDownloadScooresSheetElimination::class);
        // ================================================================================================

        // ------------------------------------------------ Archery Enterprise Service ------------------------------------------------ //

        $this->registerService("createVenuePlace", CreateVenuePlace::class);
        $this->registerService("getVenuePlace", GetVenuePlace::class);
        $this->registerService("getVenueListFacilities", GetVenueMasterPlaceFacilities::class);
        $this->registerService("getListVenuePlace", GetListVenuePlace::class);
        $this->registerService("getVenuePlaceOtherFacilitiesByEoId", GetVenuePlaceOtherFacilitiesByEoId::class);
        $this->registerService("updateIsHideOtherFacilities", UpdateIsHideOtherFacilities::class);
        $this->registerService("deleteImageVenuePlace", DeleteImageVenuePlace::class);
        $this->registerService("updateVenuePlace", UpdateVenuePlace::class);
        $this->registerService("deleteDraftVenuePlace", DeleteDraftVenuePlace::class);
        $this->registerService("addVenueScheduleOperational", AddVenueScheduleOperational::class);
        $this->registerService("getVenueScheduleOperationalDetailById", GetVenueScheduleOperationalDetailById::class);
        $this->registerService("getListVenueScheduleOperationalByPlaceId", GetListVenueScheduleOperationalByPlaceId::class);
        $this->registerService("updateVenueScheduleOperational", UpdateVenueScheduleOperational::class);
        $this->registerService("addVenueScheduleHoliday", AddVenueScheduleHoliday::class);
        $this->registerService("getVenueScheduleHolidayDetailById", GetVenueScheduleHolidayDetailById::class);
        $this->registerService("getListVenueScheduleHolidayByPlaceId", GetListVenueScheduleHolidayByPlaceId::class);
        $this->registerService("updateVenueScheduleHoliday", UpdateVenueScheduleHoliday::class);
        $this->registerService("deleteVenueScheduleHoliday", DeleteVenueScheduleHoliday::class);
        $this->registerService("getVenueListCapacityArea", GetVenueMasterPlaceCapacityArea::class);
        $this->registerService("completeVenuePlace", CompleteVenuePlace::class);


        // ------------------------------------------------ End of Archery Enterprise Service ------------------------------------------------ //



    }


    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
