<?php

$router->group(['prefix' => 'app'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userLogin']);
            $router->post('/register', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userRegister']);
            $router->post('/reset-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userResetPassword']);
            $router->post('/forgot-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userForgotPassword']);
            $router->post('/validate-code-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userValidateCodePassword']);
        });

        $router->group(['prefix' => 'archery-event', 'middleware' => 'auth.user'], function () use ($router) {
            $router->get('/my-event', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListEventByUserLogin']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDetailEventById']);
            $router->get('/my-category-event', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListCategoryByUserLogin']);
            $router->get('/my-category-event-detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventCategoryDetail']);
            $router->get('/my-category-event-member', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantMemberByCategory']);
            $router->post('/update-category-event-member', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateParticipantMember']);
        });

        $router->group(['prefix' => 'archery-score-sheet', 'middleware' => 'auth.user'], function () use ($router) {
            $router->get('/download', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadPdf']);
        });


        $router->group(['prefix' => 'archery-event-official', 'middleware' => 'auth.user'], function () use ($router) {
            $router->post('/order', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addOrderOfficial']);
            $router->get('/detail-order', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDetailOrderOfficial']);
            $router->get('/order-official', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getOrderEventOfficial']);
            $router->get('/event-official-detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventOfficialDetail']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth.user'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userLogout']);
            $router->put('/update-profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userUpdateProfile']);
            $router->put('/update-avatar', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userUpdateAvatar']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getUserProfile']);
            $router->put('/update-verifikasi', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateVerifikasiUser']);
            $router->get('/data-verifikasi', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDataUserVerifikasi']);
        });

        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->group(['prefix' => 'event-order', 'middleware' => 'auth.user'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addEventOrder']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventOrder']);
                $router->get('/check-email', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getMemberParticipantIndividual']);
                $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:detailEventOrder']);
            });

            $router->group(['prefix' => 'event-qualification-schedule', 'middleware' => 'auth.user'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventQualificationSchedule']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setEventQualificationSchedule']);
                $router->post('/unset', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:unsetEventQualificationSchedule']);
            });
            $router->group(['prefix' => 'certificate', 'middleware' => 'auth.user'], function () use ($router) {
                //$router->group(['prefix' => 'certificate'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownload']);
                $router->get('/list', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListDownloadCertificate']);
            });
            $router->group(['prefix' => 'archery-club', 'middleware' => 'auth.user'], function () use ($router) {
                // $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryClubs']);
                // $router->get('/profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProfileClub']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createArcheryClub']);
                $router->put('/update', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateArcheryClub']);
                $router->post('/join', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:joinArcheryClub']);
                $router->delete('/left', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:leftArcheryClub']);
                $router->delete('/kick', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:kickMember']);
                $router->get('/my-club', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getMyClub']);
            });

            $router->group(['prefix' => 'archery-club'], function () use ($router) {
                $router->get('/profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProfileClub']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryClubs']);
                $router->get('/get-province', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProvince']);
                $router->get('/get-city', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getCity']);
                $router->get('/get-club-member', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getAllMemberByClubId']);
            });

            $router->group(['prefix' => 'idcard'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadCard']);
            });
        });
    });
});
