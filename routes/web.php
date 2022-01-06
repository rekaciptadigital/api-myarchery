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

        $router->group(['prefix' => 'archery', 'middleware' => 'auth.admin'], function () use ($router) {
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
           
            $router->group(['prefix' => 'events'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEvent']);
                $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryEvent']);
                $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEvent']);
                $router->get('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryEvent']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEvent']);

                $router->get('/{id}/categories', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventCategory']);

                $router->get('/{id}/participants', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipant']);
                $router->get('/participant/members', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantMember']);
                $router->get('/{id}/participants/{participant_id}/scores', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantScore']);
                $router->get('/participant/member/profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantMemberProfile']);
                $router->put('/{id}/participants/{participant_id}/scores', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEvent']);
            });

            $router->group(['prefix' => 'scorer'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addParticipantMemberScore']);
                $router->get('/participant/detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findParticipantScoreBySchedule']);
            });
        });

        $router->group(['prefix' => 'event-qualification-schedule', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventQualificationScheduleByEo']);
            $router->get('/member', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventMemberQualificationScheduleByEo']);
        });

        $router->group(['prefix' => 'event-elimination', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/template', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventEliminationTemplate']);
            $router->get('/detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventElimination']);
            $router->get('/schedule', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventEliminationSchedule']);
            $router->post('/set', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setEventElimination']);
            $router->post('/schedule', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setEventEliminationSchedule']);
            $router->delete('/schedule', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:removeEventEliminationSchedule']);
        });

        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->get('/event-by-slug', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryEventBySlug']);
        });

        $router->group(['prefix' => 'event-certificate-templates', 'middleware' => 'auth.admin'], function () use ($router) {
        //$router->group(['prefix' => 'event-certificate-templates'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEventCertificateTemplates']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventCertificateTemplates']);
        });

    });
});

$router->group(['prefix' => 'eo'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'archery', 'middleware' => 'auth.admin'], function () use ($router) {

            $router->group(['prefix' => 'scoring'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryScoring']);
            });

        });
    });
});