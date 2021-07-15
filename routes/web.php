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

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'users'], function () use ($router) {
        });

        $router->group(['prefix' => 'training'], function () use ($router) {
        });

        $router->group(['prefix' => 'events'], function () use ($router) {
        });

        $router->group(['prefix' => 'competitions'], function () use ($router) {
        });

        $router->group(['prefix' => 'master'], function () use ($router) {
        });

        $router->group(['prefix' => 'config'], function () use ($router) {
        });

        $router->group(['prefix' => 'info'], function () use ($router) {
        });
    });
});
