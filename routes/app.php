<?php

$router->group(['prefix' => 'app', 'namespace' => '\App\Http\Controllers'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:userLogin']);
            $router->post('/register', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:userRegister']);
            $router->post('/reset-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:userResetPassword']);
            $router->post('/forgot-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:userForgotPassword']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth.user'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:userLogout']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getUserProfile']);
        });
    });
});
