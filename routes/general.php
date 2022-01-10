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

use App\Models\City;
use App\Models\Provinces;
use Illuminate\Http\Request;

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

    $router->group(['prefix' => 'archery-events'], function () use ($router) {
        $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventGlobal']);
    });

    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->get('/archery/scorer/participant', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScore']);
    });

    $router->group(['prefix' => 'general'], function () use ($router) {
        // $router->get('/get-province', function(){
        //     return Provinces::all();
        // });
        // $router->get('/get-city', function(Request $request){
        //     if($request->province_id){
        //         return City::where('province_id', $request->province_id)->get();
        //     }
        //     return City::all();
        // });

        $router->get('/get-province', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProvince']);
        $router->get('/get-city', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getCity']);
    });
});
