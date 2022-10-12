<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventMoreInformation;
use App\Models\City;
use App\Models\ArcheryEventParticipant;
use Illuminate\Support\Carbon;

class ArcheryEvent extends Model
{
    protected $appends = [
        'event_url', 'flat_categories', 'detail_city', 'event_status',
        'detail_admin', 'more_information', "event_price", "location_date_event"
    ];
    protected $guarded = ['id'];

    public function getEventPriceAttribute()
    {
        $response = [];
        $mix = null;
        $team = null;
        $individu = null;

        $category = ArcheryEventCategoryDetail::select("archery_event_category_details.*", "archery_master_team_categories.type")
            ->where("event_id", $this->id)
            ->join("archery_master_team_categories", "archery_master_team_categories.id", "=", "archery_event_category_details.team_category_id")
            ->get();


        if ($category->count() > 0) {
            foreach ($category as $c) {
                if (($c->type == "Individual") && $individu === null && $c->age_category_id != "Militer") {
                    $individu = [
                        "price" => $c->fee,
                        "early_bird" => $c->early_bird,
                        "end_date_early_bird" => $c->end_date_early_bird,
                        "is_early_bird" => $c->is_early_bird
                    ];
                } elseif (($c->team_category_id === "mix_team") && $mix === null) {
                    $mix = [
                        "price" => $c->fee,
                        "early_bird" => $c->early_bird,
                        "end_date_early_bird" => $c->end_date_early_bird,
                        "is_early_bird" => $c->is_early_bird
                    ];
                } elseif ((($c->type === "Team") && ($c->team_category_id !== "mix_team")) && $team === null) {
                    $team = [
                        "price" => $c->fee,
                        "early_bird" => $c->early_bird,
                        "end_date_early_bird" => $c->end_date_early_bird,
                        "is_early_bird" => $c->is_early_bird
                    ];
                } else {
                    continue;
                }
            }
        }

        $response = [
            "team" => $team,
            "individu" => $individu,
            "mix" => $mix,
        ];

        return $this->attributes['event_price'] = $response;
    }

    public function getLocationDateEventAttribute()
    {
        $datetime_start = $this->event_start_datetime;
        $full_date_start = explode(" ", $datetime_start)[0];
        $date_start = explode("-", $full_date_start)[2];

        $end_date = date('d F Y', strtotime($this->event_end_datetime));

        $response = $this->location . "," . " " . $date_start . " - " . $end_date;

        return $this->attributes['location_date_event'] = $response;
    }

    public function getDetailCityAttribute()
    {
        return $this->attributes['detail_city'] = City::find($this->city_id);
    }

    public function getDetailAdminAttribute()
    {
        $response = [];
        $admin = Admin::find($this->admin_id);

        if ($admin) {
            $response["id"] = $admin->id;
            $response["name"] = $admin->name;
            $response["email"] = $admin->email;
            $response["avatar"] = $admin->avatar;
            $response["phone_number"] = $admin->phone_number;
        }

        return $this->attributes['detail_admin'] = $response;
    }

    public function getMoreInformationAttribute()
    {
        $output = [];
        $response = [];

        $more_informations = ArcheryEventMoreInformation::where('event_id', $this->id)->get();

        if ($more_informations->count() > 0) {
            foreach ($more_informations as $information) {
                $response["id"] = $information->id;
                $response["event_id"] = $information->event_id;
                $response["title"] = $information->title;
                $response["description"] = $information->description;

                array_push($output, $response);
            }
        }

        return $this->attributes['more_information'] = $output;
    }

    public function getEventStatusAttribute()
    {
        $event_status = "";
        if (Carbon::today() < $this->event_start_datetime) {
            $event_status = "Before Event";
        } elseif (Carbon::today() > $this->event_end_datetime) {
            $event_status = "After Event";
        } else {
            $event_status = "Event Running";
        }
        return $this->attributes['event_status'] = $event_status;
    }

    public function archeryEventCategories()
    {
        return $this->hasMany(ArcheryEventCategory::class, 'event_id', 'id');
    }

    public function archeryEventQualifications()
    {
        return $this->hasMany(ArcheryEventQualification::class, 'event_id', 'id');
    }

    public function archeryEventRegistrationFees()
    {
        return $this->hasMany(ArcheryEventRegistrationFee::class, 'event_id', 'id');
    }

    public function archeryEventTargets()
    {
        return $this->hasMany(ArcheryEventTarget::class, 'event_id', 'id');
    }

    public function getQualificationSessionLengthAttribute($value)
    {
        return json_decode($value);
    }

    public function getIsFlatRegistrationFeeAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function getQualificationWeekdaysOnlyAttribute($value)
    {
        return $value == 1 || $value == '1';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    protected function getCategories($id, $type = "", $is_show = null)
    {
        $categories = ArcheryEventCategoryDetail::select(
            "archery_event_category_details.is_show",
            "archery_event_category_details.id",
            "archery_event_category_details.session_in_qualification",
            "archery_event_category_details.quota",
            "archery_event_category_details.fee",
            "archery_event_category_details.early_bird",
            "archery_event_category_details.end_date_early_bird",
            "archery_event_category_details.id AS key",
            "archery_master_age_categories.label as label_age",
            "archery_master_age_categories.id as id_age",
            "archery_master_competition_categories.label as label_competition_categories",
            "archery_master_competition_categories.id as id_competition_categories",
            "archery_master_distances.label as label_distances",
            "archery_master_distances.id as id_distances",
            "archery_master_team_categories.label as label_team_categories",
            "archery_master_team_categories.id as id_team_categories",
            "archery_master_team_categories.type as type",
            "archery_event_category_details.start_registration",
            "archery_event_category_details.end_registration",
            DB::raw("CONCAT(archery_master_team_categories.label,'-',
                                            archery_master_age_categories.label,'-',
                                            archery_master_competition_categories.label,'-',
                                            archery_master_distances.label) AS label")
        )
            ->join("archery_master_age_categories", "archery_event_category_details.age_category_id", "archery_master_age_categories.id")
            ->join("archery_master_competition_categories", "archery_event_category_details.competition_category_id", "archery_master_competition_categories.id")
            ->join("archery_master_distances", "archery_event_category_details.distance_id", "archery_master_distances.id")
            ->join("archery_master_team_categories", "archery_event_category_details.team_category_id", "archery_master_team_categories.id")
            ->where("archery_event_category_details.event_id", $id)
            ->orderBy("archery_event_category_details.created_at", "ASC")
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where("archery_master_team_categories.type", $type);
                }
            })
            ->where(function ($query) use ($is_show) {
                if ($is_show !== null) {
                    $query->where("archery_event_category_details.is_show", $is_show);
                }
            })
            ->get();


        return $categories;
    }

    public function getEventUrlAttribute()
    {
        return env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($this->admin->name) . '/' . $this->event_slug;
    }

    public function getFlatCategoriesAttribute()
    {
        $query = "
            SELECT A.id as archery_event_id,
                B.age_category_id, B.for_age, B1.label as age_category_label,
                C.competition_category_id, C1.label as competition_category_label,
                D.team_category_id, D1.label as team_category_label,
                E.distance_id, E1.label as distance_label,
                CONCAT(D1.label, ' - ', B1.label, ' - ', C1.label, ' - ', E1.label) as archery_event_category_label
            FROM archery_events A
            JOIN archery_event_categories B ON A.id = B.event_id
            JOIN archery_event_category_competitions C ON B.id = C.event_category_id
            JOIN archery_event_category_competition_teams D ON C.id = D.event_category_competition_id
            JOIN archery_event_category_competition_distances E ON C.id = E.event_category_competition_id
            JOIN archery_master_age_categories B1 ON B.age_category_id = B1.id
            JOIN archery_master_competition_categories C1 ON C.competition_category_id = C1.id
            JOIN archery_master_team_categories D1 ON D.team_category_id = D1.id
            JOIN archery_master_distances E1 ON E.distance_id = E1.id
            WHERE A.id = :event_id
            ORDER BY D1.label, B1.label, C1.label, E1.label
        ";

        $results = DB::select($query, ['event_id' => $this->id]);

        return $results;
    }
    public static function isOwnEvent($admin_id, $event_id)
    {
        $archery_event = DB::table('archery_events')->where('admin_id', $admin_id)->where('id', $event_id)->first();
        if (!$archery_event) {
            return false;
        } else {
            return true;
        }
    }

    protected function detailEventById($id, $is_show = null)
    {

        $datas = ArcheryEvent::select(
            '*',
            'archery_events.id as id_event',
            'cities.id as cities_id',
            'cities.name as cities_name',
            'provinces.id as province_id',
            'provinces.name as provinces_name',
            'admins.name as admin_name',
            'admins.email as admin_email',
            'archery_events.handbook',
            'admin_id'
        )
            ->leftJoin("cities", "cities.id", "=", "archery_events.city_id")
            ->leftJoin("provinces", "provinces.id", "=", "cities.province_id")
            ->leftJoin("admins", "admins.id", "=", "archery_events.admin_id")
            ->where('archery_events.id', $id)
            ->get();

        $more_informations = ArcheryEventMoreInformation::where('event_id', $id)->get();
        $moreinformations_data = [];
        if ($more_informations) {
            foreach ($more_informations as $key => $value) {
                $moreinformations_data[] = [
                    'id' => $value->id,
                    'event_id' => $value->event_id,
                    'title' => $value->title,
                    'description' => $value->description,
                ];
            }
        }

        $event_categories = $this->getCategories($id, "", $is_show);

        $eventcategories_data = [];
        if ($event_categories) {
            foreach ($event_categories as $key => $value) {

                $count_participant = ArcheryEventParticipant::getTotalPartisipantByEventByCategory($value->key);


                $eventcategories_data[] = [
                    'category_details_id' => $value->key,
                    'age_category_id' => [
                        'id' => $value->id_age,
                        'label' => $value->label_age
                    ],
                    'competition_category_id' => [
                        'id' => $value->id_competition_categories,
                        'label' => $value->label_competition_categories
                    ],
                    'distance_id' => [
                        'id' => $value->id_distances,
                        'label' => $value->label_distances
                    ],
                    'team_category_id' => [
                        'id' => $value->id_team_categories,
                        'label' => $value->label_team_categories
                    ],
                    'quota' => $value->quota,
                    'fee' => $value->fee,
                    'total_participant' => $count_participant,
                    "closed_register" => true,
                    "early_bird" => $value->early_bird,
                    "end_date_early_bird" => $value->end_date_early_bird,
                    "is_early_bird" => $value->is_early_bird,
                    "label" => $value->label_category,
                    "is_show" => $value->is_show,
                    "start_registration" => $value->start_registration,
                    "end_registration" => $value->end_registration
                ];
            }
        }

        if ($datas) {
            foreach ($datas as $key => $data) {
                $event_url = env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($data->admin_name) . '/' . $data->event_slug;

                $admins = Admin::where('id', $data->admin_id)->get();
                $admins_data = [];
                if ($admins) {
                    foreach ($admins as $key => $value) {
                        $admins_data = [
                            'id' => $value->id,
                            'name' => $value->name,
                            'email' => $value->email,
                            'avatar' => $value->avatar,
                        ];
                    }
                }


                $event = ArcheryEvent::find($data->id_event);

                $detail['id'] = $data->id_event;
                $detail['event_type'] = $data->event_type;
                $detail['event_competition'] = $data->event_competition;
                $detail['is_private'] = $data->is_private;
                $detail['public_information'] = [
                    'event_name' => $data->event_name,
                    'event_banner' => $data->poster,
                    'handbook' => $data->handbook,
                    'logo' => $data->logo,
                    'event_description' => $data->description,
                    'event_location' => $data->location,
                    'event_city' => [
                        'city_id' => $data->cities_id,
                        'name_city' => $data->cities_name,
                        'province_id' => $data->province_id,
                        'province_name' => $data->provinces_name
                    ],
                    'event_location_type' => $data->location_type,
                    'event_start_register' => $data->registration_start_datetime,
                    'event_end_register' => $data->registration_end_datetime,
                    'event_start' => $data->event_start_datetime,
                    'event_end' => $data->event_end_datetime,
                    'event_status' => $data->status,
                    'event_slug' => $data->event_slug,
                    'event_url' => $event_url,
                    'need_verify' => $data->need_verify,
                    'event_tracking' => $data->event_status,
                    "logo" => $data->logo,
                ];
                $detail['more_information'] = $moreinformations_data;
                $detail['event_categories'] = $eventcategories_data;
                $detail['admins'] = $admins_data;
                $detail["can_register"] = $event->getCanRegister();
            }
        }
        $end = $detail['public_information']["event_end_register"];
        $detail["closed_register"] = strtotime($end) < strtotime('now') ? true : false;
        $detail["total_participant_team"] = ArcheryEventParticipant::where("event_id", $id)->where("status", 1)->where("type", "team")->count();
        $detail["total_participant_individual"] = ArcheryEventParticipant::where("event_id", $id)->where("type", "individual")->where("status", 1)->count();
        $end_date_early_bird = null;
        $category_with_early_bird = ArcheryEventCategoryDetail::where("end_date_early_bird", "!=", null)->where("early_bird", ">", 0)->first();
        if ($category_with_early_bird) {
            $end_date_early_bird = $category_with_early_bird->end_date_early_bird;
        }

        $detail["end_date_early_bird"] = $end_date_early_bird;
        $official_status = 0;
        $official_fee = 0;
        $official_detail = ArcheryEventOfficialDetail::where("event_id", $data->id_event)->where("status", 1)->first();
        if ($official_detail) {
            $official_status = 1;
            $official_fee = $official_detail->fee;
        }
        $detail["official_status"] = $official_status;
        $detail["official_fee"] = $official_fee;
        return $detail;
    }

    public function getCanRegister()
    {
        $response = [];
        $response["event_id"] = $this->id;
        $response["default_datetime_register"] = [
            "start" => $this->registration_start_datetime,
            "end" => $this->registration_end_datetime
        ];

        $response["schedule_event"] = [
            "start" => $this->event_start_datetime,
            "end" => $this->event_end_datetime,
        ];
        $config = ConfigCategoryRegister::where("event_id", $this->id)->get();
        $enable_config = 0;
        if ($config->count() > 0) {
            $enable_config = 1;
        }

        $response["enable_config"] = $enable_config;

        foreach ($config as $c) {
            if ($c->is_have_special == 1) {
                $list_special_config = [];
                $config_special_mapping = ConfigSpecialMaping::where("config_id", $c->id)->get();
                foreach ($config_special_mapping as $csm) {
                    $categories = [];
                    $config_special_category_mapping = ConfigSpecialCategoryMaping::where("special_config_id", $csm->id)->get();
                    foreach ($config_special_category_mapping as $cscm) {
                        $label = ArcheryEventCategoryDetail::getCategoryLabelComplete($cscm->category_id);
                        $cscm->label = $label;
                        $categories[] = $cscm;
                    }
                    $csm->categories = $categories;
                    $list_special_config[] = $csm;
                }
                $c->list_special_config = $list_special_config;
            }
            $response["list_config"][] = $c;
        }

        $can_register = 0;

        if (
            time() >= strtotime($response["default_datetime_register"]["start"])
            && time() <= strtotime($response["default_datetime_register"]["end"])
        ) {
            $can_register = 1;
        }

        if ($response["enable_config"] == 1) {
            foreach ($response["list_config"] as $c) {
                if ($c["is_have_special"] == 0) {
                    if (
                        time() >= strtotime($c["datetime_start_register"])
                        && time() <= strtotime($c["datetime_end_register"])
                    ) {
                        $can_register = 1;
                    }
                } else {
                    foreach ($c["list_special_config"] as $lsc) {
                        if (
                            time() >= strtotime($lsc["datetime_start_register"])
                            && time() <= strtotime($lsc["datetime_end_register"])
                        ) {
                            $can_register = 1;
                        }
                    }
                }
            }
        }

        return $can_register;
    }

    protected function detailEventAll($limit, $offset, $event_name = "")
    {

        $datas = ArcheryEvent::select(
            '*',
            'archery_events.id as id_event',
            'cities.id as cities_id',
            'cities.name as cities_name',
            'provinces.id as province_id',
            'provinces.name as provinces_name',
            'admins.name as admin_name',
            'admins.email as admin_email',
            'admin_id'
        )
            ->leftJoin("cities", "cities.id", "=", "archery_events.city_id")
            ->leftJoin("provinces", "provinces.id", "=", "cities.province_id")
            ->leftJoin("admins", "admins.id", "=", "archery_events.admin_id")
            ->where(function ($query) use ($event_name) {
                if (!empty($event_name)) {
                    $query->where('archery_events.event_name', 'like', '%' . $event_name . '%');
                }
            })
            ->limit($limit)->offset($offset)
            ->get();

        $output = [];
        foreach ($datas as $key => $data) {

            $event_url = env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($data->admin_name) . '/' . $data->event_slug;

            $admins = Admin::where('id', $data->admin_id)->get();
            $admins_data = [];
            if ($admins) {
                foreach ($admins as $key => $value) {
                    $admins_data = [
                        'id' => $value->id,
                        'name' => $value->name,
                        'email' => $value->email,
                        'avatar' => $value->avatar,
                    ];
                }
            }



            $more_informations = ArcheryEventMoreInformation::where('event_id', $data->id_event)->get();
            $moreinformations_data = [];
            if ($more_informations) {
                foreach ($more_informations as $key => $value) {
                    $moreinformations_data[] = [
                        'id' => $value->id,
                        'event_id' => $value->event_id,
                        'title' => $value->title,
                        'description' => $value->description,
                    ];
                }
            }

            $event_categories = $this->getCategories($data->id_event);

            $eventcategories_data = [];
            if ($event_categories) {
                foreach ($event_categories as $key => $value) {
                    $eventcategories_data[] = [
                        'category_details_id' => $value->key,
                        'age_category_id' => [
                            'id' => $value->id_age,
                            'label' => $value->label_age
                        ],
                        'competition_category_id' => [
                            'id' => $value->id_competition_categories,
                            'label' => $value->label_competition_categories
                        ],
                        'distance_id' => [
                            'id' => $value->id_distances,
                            'label' => $value->label_distances
                        ],
                        'team_category_id' => [
                            'id' => $value->id_team_categories,
                            'label' => $value->label_team_categories
                        ],
                        'quota' => $value->quota,
                        'fee' => $value->fee,
                    ];
                }
            }

            $output[] = array(
                "id" => $data->id_event,
                "event_type" => $data->event_type,
                "event_competition" => $data->event_competition,
                "public_information" => [
                    'event_name' => $data->event_name,
                    'event_banner' => $data->poster,
                    'event_description' => $data->description,
                    'event_location' => $data->location,
                    'event_city' => [
                        'city_id' => $data->cities_id,
                        'name_city' => $data->cities_name,
                        'province_id' => $data->province_id,
                        'province_name' => $data->provinces_name
                    ],
                    'event_location_type' => $data->location_type,
                    'event_start_register' => $data->registration_start_datetime,
                    'event_end_register' => $data->registration_end_datetime,
                    'event_start' => $data->event_start_datetime,
                    'event_end' => $data->event_end_datetime,
                    'event_status' => $data->status,
                    'event_slug' => $data->event_slug,
                    'event_url' => $event_url
                ],
                'more_information' => $moreinformations_data,
                'event_categories' => $eventcategories_data,
                'admins' => $admins_data

            );

            unset($moreinformations_data);
            unset($eventcategories_data);
        }

        return $output;
    }

    public function getDetailEventById($event_id)
    {
        $data = ArcheryEvent::find($event_id);
        if (!$data) {
            return null;
        }

        $event_url = env('WEB_DOMAIN', 'https://my-archery.id') . '/event/' . Str::slug($data->admin_name) . '/' . $data->event_slug;

        $admins = Admin::where('id', $data->admin_id)->get();
        $admins_data = [];
        if ($admins) {
            foreach ($admins as $key => $value) {
                $admins_data = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'email' => $value->email,
                    'avatar' => $value->avatar,
                ];
            }
        }

        $more_informations = ArcheryEventMoreInformation::where('event_id', $data->id)->get();
        $moreinformations_data = [];
        if ($more_informations) {
            foreach ($more_informations as $key => $value) {
                $moreinformations_data[] = [
                    'id' => $value->id,
                    'event_id' => $value->event_id,
                    'title' => $value->title,
                    'description' => $value->description,
                ];
            }
        }

        $city = City::find($data->city_id);
        $output = [
            "id" => $data->id,
            "event_type" => $data->event_type,
            "event_competition" => $data->event_competition,
            "is_private" => $data->is_private,
            "public_information" => [
                'event_name' => $data->event_name,
                'event_banner' => $data->poster,
                'event_description' => $data->description,
                'event_location' => $data->location,
                'event_city' => [
                    'city_id' => $city ? $city->id : null,
                    'name_city' => $city ? $city->name : null,
                    'province_id' => $city ? Provinces::find($city->province_id)->id : null,
                    'province_name' => $city ? Provinces::find($city->province_id)->name : null
                ],
                'event_location_type' => $data->location_type,
                'event_start_register' => $data->registration_start_datetime,
                'event_end_register' => $data->registration_end_datetime,
                'event_start' => $data->event_start_datetime,
                'event_end' => $data->event_end_datetime,
                'event_status' => $data->status,
                'event_slug' => $data->event_slug,
                'event_url' => $event_url
            ],
            'more_information' => $moreinformations_data,
            'admins' => $admins_data

        ];

        unset($moreinformations_data);
        unset($eventcategories_data);


        return $output;
    }
}
