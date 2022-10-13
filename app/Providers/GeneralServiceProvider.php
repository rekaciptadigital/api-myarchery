<?php

namespace App\Providers;

use App\BLoC\General\Dos\GetMedalParticipantByEventId;
use App\BLoC\General\GetCityCountry;
use App\BLoC\General\GetCountry;
use App\BLoC\General\GetListTabCategoryByEventId;
use App\BloC\General\Series\ExportMemberSeriesRank;
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
        $this->registerService("getCountry", GetCountry::class);
        $this->registerService("getMedalParticipantByEventId", GetMedalParticipantByEventId::class);
        $this->registerService("getListTabCategoryByEventId", GetListTabCategoryByEventId::class);
        $this->registerService("getParticipantScoreEliminationSelectionLiveScore", GetParticipantScoreEliminationSelectionLiveScore::class);
        $this->registerService("getParticipantScoreEventSelectionLiveScore", GetParticipantScoreEventSelectionLiveScore::class);

        // ==================================== Fast Open 3 ========================================
        $this->registerService("exportMemberSeriesRank", ExportMemberSeriesRank::class);
        // ==================================== End ================================================
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
