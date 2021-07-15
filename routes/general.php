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


$router->group(['prefix' => 'api', 'namespace' => '\App\Http\Controllers\General'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', 'AuthController@login');
            $router->post('/register', 'AuthController@register');
            $router->put('/reset-password', 'AuthController@resetPassword');
            $router->put('/forgot-password', 'AuthController@forgotPassword');
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth'], function () use ($router) {
            $router->post('/logout', 'UserController@logout');
            $router->get('/', 'UserController@index');
            $router->put('/change-password', 'UserController@changePassword');
            $router->put('/profile', 'UserController@updateProfile');
            $router->put('/profile-image', 'UserController@updateProfileImage');
        });
    });
});
