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

use App\Models\ArcheryEventElimination;
use App\Models\ArcheryEventEliminationMatch;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use App\Models\ArcheryEventParticipantMemberNumber;
use App\Models\ArcheryUserAthleteCode;
use App\Models\City;
use App\Models\Provinces;
use App\Models\User;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

$router->get("kioheswbgcgoiwagfp/{id}", function ($id) {
    try {
        $user = User::find($id);
        if (!$user) {
            throw new Exception("user not found", 404);
        }
        $province = Provinces::orderBy("name")->get();

        $city_user = City::where("id", $user->address_city_id)->first();
        $province_user = Provinces::where("id", $user->address_province_id)->first();
        // return $user;
        // return $province;
        return view("athlete_code.change_domicile", [
            "user" => $user,
            "province" => $province,
            "province_user" => $province_user,
            "city_user" => $city_user
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            "status" => "error",
            "message" => $th->getMessage()
        ], $th->getCode());
    }
});

$router->put("kioheswbgcgoiwagfp/{id}", function (Request $request, $id) {
    DB::beginTransaction();
    try {
        $user = User::find($id);
        if (!$user) {
            throw new Exception("user not found", 404);
        }

        if ($user->verify_status != 1) {
            throw new Exception("user status not verify", 400);
        }

        if ($request->input("province") && $request->input("city")) {
            $province = Provinces::find($request->input("province"));
            if (!$province) {
                throw new Exception("province not found", 404);
            }

            $city = City::find($request->input("city"));
            if (!$city) {
                throw new Exception("city not found", 404);
            }

            $user->address_province_id = $province->id;
            $user->address_city_id = $city->id;
            $user->save();

            $athlete_code = ArcheryUserAthleteCode::where("user_id", $user->id)
                ->where("status", 1)
                ->update(["status" => 0]);
            if (!$athlete_code) {
                throw new Exception("code not set for this user", 404);
            }

            // $athlete_code->status = 0;
            // $athlete_code->save();

            if ($city->prefix == null) {
                throw new Exception("prefix not set", 404);
            }

            ArcheryUserAthleteCode::saveAthleteCode(ArcheryUserAthleteCode::makePrefix($city->prefix), $user->id, $city->prefix);
            $date = new DateTime();
            $member_list = ArcheryEventParticipant::select("archery_event_participant_members.*", "archery_event_participants.event_id")
                ->join("archery_event_participant_members", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                ->join("archery_events", "archery_events.id", "=", "archery_event_participants.event_id")
                ->where("archery_event_participants.status", 1)
                ->where("archery_event_participant_members.user_id", $user->id)
                ->whereDate("archery_events.event_end_datetime", ">", $date)
                ->get();

            if ($member_list->count() > 0) {
                foreach ($member_list as $key => $value) {
                    $member = ArcheryEventParticipantMember::find($value->id);
                    if (!$member) {
                        throw new Exception("member not found", 404);
                    }

                    $member->city_id = $user->address_city_id;
                    $member->save();
                }
            }
        }
        DB::commit();
        return redirect("kioheswbgcgoiwagfp/" . $user->id);
    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json([
            "status" => "error",
            "message" => $th->getMessage()
        ]);
    }
});

$router->get("mas_adit", function () {
    $series1 =  User::select("users.*")->join("archery_event_participants", "archery_event_participants.user_id", "=", "users.id")
        ->where("archery_event_participants.event_id", 21)->where("archery_event_participants.status", 1)
        ->where("archery_event_participants.type", "individual")
        ->where("users.address_province_id", 31)->distinct()
        ->get();

    $series2 =  User::select("users.*")->join("archery_event_participants", "archery_event_participants.user_id", "=", "users.id")
        ->where("archery_event_participants.event_id", 22)->where("archery_event_participants.status", 1)
        ->where("archery_event_participants.type", "individual")
        ->where("users.address_province_id", 31)->distinct()
        ->get();

    $irisan = [];
    foreach ($series1 as $key1 => $value1) {
        foreach ($series2 as $key2 => $value2) {
            if ($value1->id === $value2->id) {
                array_push($irisan, $value1);
            }
        }
    }

    return [
        "series_1" => $series1->count(),
        "series_2" => $series2->count(),
        "irisan" => count($irisan)
    ];
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
                $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:addArcheryEventOfficialDetail']);
                $router->get('/get-all-member', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getAllArcheryEventOfficial']);
                $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editArcheryEventOfficialDetail']);
                $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventOfficialDetail']);
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

        $router->group(['prefix' => 'dashboard-dos'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getArcheryEventScheduleDashboardDos']);
            $router->get('/category-details', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListCategoryByEventId']);
            $router->get('/elimination-template', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getEventEliminationTemplate']);
            $router->get('/download-score-qualification', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadScoreQualification']);
            $router->get('/download-elimination', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:downloadEliminationDashboardDos']);
            $router->get('/scorer-qualification', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScoreQualificationDos']);
        });
    });

    // ============================================ v2 =======================================================

    $router->group(['prefix' => 'v2'], function () use ($router) {
        $router->group(['prefix' => 'events', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createArcheryEventV2']);
            $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateArcheryEventV2']);
        });

        $router->group(['prefix' => 'bud-rest', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getBudRestV2']);
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createOrUpdateBudRestV2']);
            $router->get('/get-list-budrest', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getListBudRestV2']);
        });

        $router->group(['prefix' => 'category', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createOrUpdateArcheryCategoryDetailV2']);
            $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteCategoryDetailV2']);
        });
        $router->group(['prefix' => 'members', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:listMemberV2']);
            $router->get('/team', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:listMemberTeamV2']);
            $router->get('/access-categories', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getMemberAccessCategories']);
        });

        $router->group(['prefix' => 'qualification-time', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createQualificationTimeV2']);
        });

        $router->group(['prefix' => 'q-and-a', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createQandA']);
            $router->delete('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:deleteQandA']);
            $router->get('/detail', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getQandADetail']);
            $router->put('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:editQandA']);
        });

        $router->group(['prefix' => 'schedule-full-day', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getScheduleFullDay']);
            $router->put('/change_bud_rest', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:updateMemberBudrest']);
        });

        $router->group(['prefix' => 'scorer-qualification', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->get('/', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getParticipantScoreQualificationV2']);
        });

        $router->group(['prefix' => 'id-card', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/template', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:createOrUpdateIdCardTemplateV2']);
            $router->get('/template-by-event-id', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:getTemplateIdCardByEventIdV2']);
            $router->get('/download-by-category', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:bulkDownloadIdCardByCategoryIdV2']);
            $router->get('/find-id-card-by-code', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:findIdCardByMmeberOrOfficialId']);
        });

        $router->group(['prefix' => 'participant', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->put('/change-is-present', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:changeIsPresent']);
            $router->post('/insert-by-admin', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:insertParticipantByAdmin']);
        });

        $router->group(['prefix' => 'event-elimination', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/set', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setEventEliminationV2']);
            $router->put('/set-count-participant-elimination', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setEventEliminationCountParticipant']);
            $router->post('/set-budrest', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setBudRestElimination']);
            $router->post('/clean-elimination-scoring', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:cleanEliminationMatch']);
        });

        $router->group(['prefix' => 'scorer-elimination', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/set-admin-total', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setAdminTotal']);
            $router->post('/set-save-permanent', ['uses' => 'BLoCController@execute', 'middleware' => 'bloc:setSavePermanentElimination']);
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
});
