<?php

namespace App\Providers;

use App\BLoC\General\Certificate\BulkDownloadWinerCertificateByEventId;
use App\BLoC\General\Dos\GetMedalParticipantByEventId;
use App\BLoC\General\ExportClubRankedGroupByTeamCategory;
use App\BLoC\General\ExportmemberCollective;
use App\BLoC\General\ExportMemberCollectiveTeam;
use App\BLoC\General\GetCityCountry;
use App\BLoC\General\GetCountry;
use App\BLoC\General\GetListTabCategoryByEventId;
use App\BLoC\General\GetProvinceCountry;
use App\BLoC\General\ImportMemberCollective;
use App\BLoC\General\ImportMemberCollectiveTeam;
use App\BLoC\General\InsertDataParticipantToClassificationEvent;
use App\BLoC\General\Series\ExportMemberSeriesRank;
use App\BLoC\General\Support\DownloadTemplateMemberCollectiveClub;
use App\BLoC\General\Support\ImportMemberCollectiveClub;
use App\BLoC\General\Support\UpdateLogoCity;
use App\BLoC\General\Support\UpdateStatusPayment;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreEliminationSelectionLiveScore;
use App\BLoC\Web\ArcheryScoring\GetParticipantScoreEventSelectionLiveScore;
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
        $this->registerService("getProvinceCountry", GetProvinceCountry::class);
        $this->registerService("getCountry", GetCountry::class);
        $this->registerService("getMedalParticipantByEventId", GetMedalParticipantByEventId::class);
        $this->registerService("getListTabCategoryByEventId", GetListTabCategoryByEventId::class);
        $this->registerService("getParticipantScoreEliminationSelectionLiveScore", GetParticipantScoreEliminationSelectionLiveScore::class);
        $this->registerService("getParticipantScoreEventSelectionLiveScore", GetParticipantScoreEventSelectionLiveScore::class);
        $this->registerService("updateLogoCity", UpdateLogoCity::class);

        // ==================================== Fast Open 3 ========================================
        $this->registerService("exportMemberSeriesRank", ExportMemberSeriesRank::class);
        // ==================================== End ================================================

        $this->registerService("downloadTemplateMemberCollectiveClub", DownloadTemplateMemberCollectiveClub::class);

        // ===================================== queen archery =====================================
        $this->registerService("exportClubRankedGroupByTeamCategory", ExportClubRankedGroupByTeamCategory::class);
        $this->registerService("exportmemberCollective", ExportmemberCollective::class);
        $this->registerService("importMemberCollective", ImportMemberCollective::class);
        $this->registerService("importMemberCollectiveTeam", ImportMemberCollectiveTeam::class);
        $this->registerService("exportMemberCollectiveTeam", ExportMemberCollectiveTeam::class);
        $this->registerService("importMemberCollectiveClub", ImportMemberCollectiveClub::class);
        $this->registerService("updateStatusPayment", UpdateStatusPayment::class);


        // new condition register with classification or contingent history
        $this->registerService("insertDataParticipantToClassificationEvent", InsertDataParticipantToClassificationEvent::class);
        
        $this->registerService("bulkDownloadWinerCertificateByEventId", BulkDownloadWinerCertificateByEventId::class);

        // ======================================= End ==============================================
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
