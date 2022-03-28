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

use App\Models\ArcheryUserAthleteCode;
use App\Models\City;
use App\Models\Provinces;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Http\Request;

$router->get('kioheswbgcgoiwagfp', function () {
    $data = User::where('verify_status', 3)->get();
    $data2 = User::where('verify_status', 1)->orderBy("address_city_id", "DESC")->get();

    if ($data->count() > 0) {
        foreach ($data as $d1) {
            $d1['province'] = Provinces::find($d1->address_province_id);
            $d1['city'] = City::find($d1->address_city_id);
        }
    }

    if ($data2->count() > 0) {
        foreach ($data2 as $d2) {
            $d2['prefix'] = ArcheryUserAthleteCode::getAthleteCode($d2->id);
            $d2['province'] = Provinces::find($d2->address_province_id);
            $d2['city'] = City::find($d2->address_city_id);
        }
    }
    return view('athlete_code/index', [
        "data" => $data,
        "data2" => $data2
    ]);
});

$router->post('accept', function (Request $request) {
    $user_id = $request->input('user_id');
    $user = User::findOrFail($user_id);
    $user->verify_status = 1;
    $user->date_verified = new DateTime();

    $trim_nik = trim($user->nik, " ");
    $substr = substr($trim_nik, 0, 4);

    $city = City::where("id", $user->address_city_id)->first();
    if (!$city) {
        throw new BLoCException("nik not valid");
    }
    $city_code = $city->prefix;

    if ($city_code == null) {
        throw new BLoCException("prefix not set");
    }

    ArcheryUserAthleteCode::saveAthleteCode(ArcheryUserAthleteCode::makePrefix($city_code), $user->id, $city_code);
    $user->save();
    return redirect('kioheswbgcgoiwagfp');
});

$router->post('reject', function (Request $request) {
    $user_id = $request->input('user_id');
    $user = User::findOrFail($user_id);
    $user->verify_status = 2;
    $user->reason_rejected = $request->input('reason') ? $request->input('reason') : "pastikan data sudah benar";
    $user->save();

    // $city = City::find($user->address_city_id);
    // ArcheryUserAthleteCode::saveAthleteCode(ArcheryUserAthleteCode::makePrefix($city->prefix), $user->id);
    return redirect('kioheswbgcgoiwagfp');
});

$router->group(['prefix' => 'web'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:login']);
            $router->post('/register', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:register']);
            $router->post('/reset-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:resetPassword']);
            $router->post('/forgot-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:forgotPassword']);
            $router->post('/validate-code-password', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:validateCodePassword']);
        });

        $router->group(['prefix' => 'archery-score-sheet', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/download', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadPdf']);
            $router->get('/score-sheet-elimination', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadEliminationScoreSheet']);
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

            $router->group(['prefix' => 'category-details'], function () use ($router) {
                $router->get('/qualification', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryCategoryDetailQualification']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryCategoryDetail']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryCategoryDetail']);
                $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryCategoryDetail']);
                $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryCategoryDetail']);
            });

            $router->group(['prefix' => 'bud-rest'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setBudRest']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getBudRest']);
            });

            $router->group(['prefix' => 'official'], function () use ($router) {
                $router->get('/download', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadArcheryEventOfficial']);
            });

            $router->group(['prefix' => 'qualification-time'], function () use ($router) {
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEventQualificationTime']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventQualificationTime']);
            });

            $router->group(['prefix' => 'team-categories'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventMasterTeamCategory']);
            });

            $router->group(['prefix' => 'distance-categories'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventMasterDistanceCategory']);
            });

            $router->group(['prefix' => 'competition-categories'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventMasterCompetitionCategory']);
            });

            $router->group(['prefix' => 'more-information'], function () use ($router) {
                $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEventMoreInformation']);
                $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryEventMoreInformation']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEventMoreInformation']);
            });

            $router->group(['prefix' => 'age-categories'], function () use ($router) {
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventMasterAgeCategory']);
            });

            $router->group(['prefix' => 'events'], function () use ($router) {
                $router->put('/delete-handbook', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteHandBook']);
                $router->put('/category-fee', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEventCategoryDetailFee']);
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEvent']);
                $router->delete('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteArcheryEvent']);
                $router->put('/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEvent']);
                $router->get('/find/{id}', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryEvent']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEvent']);
                $router->get('/all/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListArcheryEventDetail']);
                $router->get('/{id}/categories', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventCategory']);
                $router->get('/{id}/participants', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipant']);
                $router->get('/participant/members', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantMember']);
                $router->get('/{id}/participants/{participant_id}/scores', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantScore']);
                $router->get('/participant/member/profile', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventParticipantMemberProfile']);
                $router->put('/{id}/participants/{participant_id}/scores', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEvent']);
                $router->get('/participant/excel/download/lunas', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadArcheryEventParticipantLunas']);
                $router->get('/participant/excel/download/pending', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadArcheryEventParticipantPending']);



                $router->get('/participant/excel/download', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadArcheryEventParticipant']);
                $router->post('/update-status', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateArcheryEventStatus']);
                $router->get('/detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventDetailById']);
                $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEventSeparated']);
                $router->put('/category-fee', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEventCategoryDetailFee']);
                $router->get('/bulk-download-card', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDownloadCard']);
                $router->get('/add-edit-idcard', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addUpdateArcheryEventIdCard']);
                $router->get('/report-result', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryReportResult']);
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


        $router->group(['prefix' => 'participant', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->put('/update-category', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateParticipantCategory']);
            $router->post('/refund', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:refund']);
        });

        $router->group(['prefix' => 'series', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/download/user-points', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getDownloadUserSeriePoint']);
        });

        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->get('/event-by-slug', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findArcheryEventBySlug']);

            $router->group(['prefix' => 'events'], function () use ($router) {
                $router->get('/detail-by-slug', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventDetailBySlug']);
                $router->get('/register/list-categories', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventCategoryRegister']);
            });
        });

        $router->group(['prefix' => 'event-certificate-templates', 'middleware' => 'auth.admin'], function () use ($router) {
            //$router->group(['prefix' => 'event-certificate-templates'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEventCertificateTemplates']);
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventCertificateTemplates']);
        });
    });

    $router->group(['prefix' => 'v2'], function () use ($router) {
        $router->group(['prefix' => 'events', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createArcheryEventV2']);
            $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateArcheryEventV2']);
        });
        $router->group(['prefix' => 'category', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createOrUpdateArcheryCategoryDetailV2']);
            $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteCategoryDetailV2']);
        });
        $router->group(['prefix' => 'members', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:listMemberV2']);
            $router->get('/access-categories', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getMemberAccessCategories']);
        });

        $router->group(['prefix' => 'qualification-time', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createQualificationTimeV2']);
        });

        $router->group(['prefix' => 'q-and-a', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createQandA']);
            $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteQandA']);
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
