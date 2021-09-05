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

$router->group(['prefix' => 'web'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:login']);
            $router->post('/register', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:register']);
            $router->post('/reset-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:resetPassword']);
            $router->post('/forgot-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:forgotPassword']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:logout']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getProfile']);
        });

        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->group(['prefix' => 'age-categories'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryAgeCategory']);
                $router->delete('/bulk', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryAgeCategory']);
                $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryAgeCategory']);
                $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryAgeCategory']);
                $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryAgeCategory']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryAgeCategory']);
            });
            $router->group(['prefix' => 'categories'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryCategory']);
                $router->delete('/bulk', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryCategory']);
                $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryCategory']);
                $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryCategory']);
                $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryCategory']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryCategory']);
            });
            $router->group(['prefix' => 'clubs'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryClub']);
                $router->delete('/bulk', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryClub']);
                $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryClub']);
                $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryClub']);
                $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryClub']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryClub']);
            });
            $router->group(['prefix' => 'events'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEvent']);
                // $router->delete('/bulk', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryClub']);
                // $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryClub']);
                // $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryClub']);
                // $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryClub']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEvent']);
            });
        });
    });
});
