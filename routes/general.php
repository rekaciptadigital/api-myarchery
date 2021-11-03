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
    
    $router->group(['prefix' => 'event-elimination'], function () use ($router) {
        $router->get('/template', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventEliminationTemplate']);
    });

    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->get('/archery/scorer/participant', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScore']);
    });
});
