<?php

$router->group(['prefix' => 'app'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userLogin']);
            $router->post('/register', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userRegister']);
            $router->post('/reset-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userResetPassword']);
            $router->post('/forgot-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userForgotPassword']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth.user'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:userLogout']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getUserProfile']);
        });
        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->group(['prefix' => 'event-order', 'middleware' => 'auth.user'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addEventOrder']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventOrder']);
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
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryClubs']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createArcheryClub']);
                $router->post('/join', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:joinArcheryClub']);
                $router->delete('/left', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:leftArcheryClub']);
            });
        });
    });
});