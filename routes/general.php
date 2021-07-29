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


$router->group(['prefix' => 'api', 'namespace' => '\App\Http\Controllers'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:login']);
            $router->post('/register', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:register']);
            $router->post('/reset-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:resetPassword']);
            $router->post('/forgot-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:forgotPassword']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:logout']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getUserProfile']);
        });
    });
});
