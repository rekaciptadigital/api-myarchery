<?php

$router->group(['prefix' => 'app', 'namespace' => '\App\Http\Controllers'], function () use ($router) {
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
        });

        $router->group(['prefix' => 'scorer'], function () use ($router) {
            $router->get('/participant', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findParticipantDetail']);
            $router->post('/edit-participant-profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:gditParticipantProfile']);
            $router->get('/ends', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEnd']);
            $router->get('/end-details', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEndDetail']);
            $router->post('/scores', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addParticipantScore']);
            $router->get('/score-summary', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getScoreSummary']);
        });
    });
});
