<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/midtrans/notification/callback', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:callbackMidtrans']);


$router->group(['prefix' => 'api', 'namespace' => '\App\Http\Controllers'], function () use ($router) {
    $router->get('display', [
        'as' => 'api_display', 'uses' => 'Controller@display'
    ]);
    $router->get('download', [
        'as' => 'api_download', 'uses' => 'Controller@download'
    ]);

    $router->post("/bulk-insert-member-contingent-excell", ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:importMemberCollective']);
    $router->post("/bulk-insert-member-club-excell", ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:importMemberCollectiveClub']);
    $router->post("/bulk-insert-member-contingent-team-excell", ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:importMemberCollectiveTeam']);
    $router->post("/update-payment-status-oy", ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateStatusPayment']);
    $router->group(['prefix' => 'download-template'], function () use ($router) {
        $router->post('/member-contingent', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:exportmemberCollective']);
        $router->post('/member-contingent-team', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:exportMemberCollectiveTeam']);
        $router->get('/member-club', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadTemplateMemberCollectiveClub']);
    });

    $router->group(['prefix' => 'event-elimination'], function () use ($router) {
        $router->get('/template', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventEliminationTemplate']);
    });

    $router->group(['prefix' => 'certificate'], function () use ($router) {
        $router->get('/BulkDownloadWinerCertificateByEventId', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDownloadWinerCertificateByEventId']);
    });

    $router->group(['prefix' => 'event-ranked'], function () use ($router) {
        $router->get('/club', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventClubRanked']);
        $router->get('/exportClubRankedGroupByTeamCategory', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:exportClubRankedGroupByTeamCategory']);
    });

    $router->group(['prefix' => 'archery-events'], function () use ($router) {
        $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventGlobal']);
    });

    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->get('/archery/scorer/participant', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScore']);
        $router->get('/archery/scorer/qualificaiton', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScoreQualification']);
        $router->get('/archery/scorer/elimination-selection', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScoreEliminationSelectionLiveScore']);
        $router->get('/archery/scorer/all-result-selection', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScoreEventSelectionLiveScore']);
    });

    $router->group(['prefix' => 'general'], function () use ($router) {
        // =============================== Fast Open 3 ============================================
        $router->group(["prefix" => "series"], function () use ($router) {
            $router->get("download-excell-member-series-rank", ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:exportMemberSeriesRank']);
        });
        // ================================= End ====================================================

        $router->group(['prefix' => 'revision-flow'], function () use ($router) {
            $router->get('/register-with-classification-event', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:insertDataParticipantToClassificationEvent']);
        });

        $router->get('/get-province', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProvince']);
        $router->get('/get-city', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getCity']);
        $router->get('/list-official', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListOfficial']);
        $router->get('/get-province-country', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProvinceCountry']);
        $router->get('/get-city-country', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getCityCountry']);
        $router->get('/get-country', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getCountry']);
        $router->get('/get-winer-participant-by-event-id', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getMedalParticipantByEventId']);
        $router->get('/get-list-tab-category-by-event-id', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListTabCategoryByEventId']);
        $router->post('/add-all-category-event-to-series', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:insertAllCategoryEventToSeries']);
        $router->post('/update-logo-city', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateLogoCity']);
    });
});

$router->group(['prefix' => 'general'], function () use ($router) {
    $router->group(['prefix' => 'v2'], function () use ($router) {
        $router->group(['prefix' => 'q-and-a'], function () use ($router) {
            $router->get('/get-by-event_id', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getQandAByEventId']);
        });

        $router->group(['prefix' => 'category-details'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListCategoryByEventId']);
        });

        $router->group(['prefix' => 'events'], function () use ($router) {
            $router->get('/by-slug', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDetailEventBySlugV2']);
            $router->get('/by-id', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDetailEventByIdGeneral']);
        });
    });
});
