<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\ArcherySerieCity;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ArcheryEventCategoryDetail extends Model
{
    protected $table = 'archery_event_category_details';
    protected $guarded = ['id'];
    protected $appends = [
        'category_team', 'event_name', 'gender_category', 'start_event',
        'is_early_bird', 'label_category', 'class_category', 'end_event',
    ];
    const INDIVIDUAL_TYPE = "Individual";
    const TEAM_TYPE = "Team";

    /*
        digunakan untuk menangkap type category tersebut

        return: "Individual" || "Team"
    */
    public function getCategoryType()
    {
        $type = null;
        $team = ArcheryEventMasterTeamCategory::where('id', $this->team_category_id)->first();
        if ($team) {
            $type = $team->type;
        }

        return $type;
    }

    public function getCategoryDetailById($category_id)
    {
        $user = Auth::guard('app-api')->user();
        $category = ArcheryEventCategoryDetail::find($category_id);
        $age_category_detail = ArcheryMasterAgeCategory::find($category->age_category_id);
        $competition_category_detail = ArcheryMasterCompetitionCategory::find($category->competition_category_id);
        $distance_detail = ArcheryMasterDistanceCategory::find($category->distance_id);
        $team_category_details = ArcheryMasterTeamCategory::find($category->team_category_id);
        $archery_event_series = ArcheryEventSerie::where("event_id", $category->event_id)->first();
        $serie_id  = 0;
        $join_serie_category_id  = 0;
        $can_join_series  = 0;
        if ($archery_event_series) {
            $serie_id  = $archery_event_series->serie_id;
        }
        $have_series = 0;
        $archerySeriesCategory = ArcherySeriesCategory::where("age_category_id", $category->age_category_id)
            ->where("competition_category_id", $category->competition_category_id)
            ->where("distance_id", $category->distance_id)
            ->where("team_category_id", $category->team_category_id)
            ->where("serie_id", $serie_id)
            ->first();
        if ($archerySeriesCategory) {
            $have_series = 1;
            if ($user) {
                if ($user->verify_status == 1) {
                    $check_serie_city = ArcherySerieCity::where("city_id", $user->address_city_id)
                        ->where("serie_id", $serie_id)
                        ->first();
                    if ($check_serie_city) {
                        $can_join_series = 1;
                        $check_join_serie = ArcheryEventParticipantMember::select("archery_event_participants.event_category_id")
                            ->join("archery_event_participants", "archery_event_participant_members.archery_event_participant_id", "=", "archery_event_participants.id")
                            ->where("archery_event_participant_members.user_id", $user->id)
                            ->where("archery_event_participants.event_id", $category->event_id)
                            ->where("archery_event_participant_members.is_series", 1)
                            ->first();
                        if ($check_join_serie) {
                            $join_serie_category_id = $check_join_serie->event_category_id;
                        }
                    }
                }
            }
        }


        $end_date_event = $this->end_event;
        $can_update_series = 0;
        if (date("Y-m-d") <= date('Y-m-d', strtotime($end_date_event . env("CAN_UPDATE_SERIES", "+ 3 days"))) && $have_series == 1) {
            $can_update_series = 1;
        }
        $output = [
            "id" => $category->id,
            "quota" => $category->quota,
            "fee" => $category->fee,
            "gender_category" => $category->gender_category,
            "category_label" => $age_category_detail->label . "-" . $team_category_details->label . "-" . $distance_detail->label,
            "category_type" => $category->category_team,
            "have_series" => $have_series,
            "can_update_series" => $can_update_series,
            "join_serie_category_id" => $join_serie_category_id,
            "can_join_series" => $can_join_series,
            "category_team" => [
                "id" => $team_category_details->id,
                "label" => $team_category_details->label
            ],
            "age_category_detail" => [
                "id" => $age_category_detail->id,
                "label" => $age_category_detail->label,
                "max_age" => $age_category_detail->max_age
            ],
            "competition_category_detail" => [
                "id" => $competition_category_detail->id,
                "label" => $competition_category_detail->label,
            ],
            "distance_detail" => [
                "id" => $distance_detail->id,
                "label" => $distance_detail->label
            ],
            "team_category_detail" => [
                "id" => $team_category_details->id,
                "label" => $team_category_details->type,
                "type" => $team_category_details->type
            ]
        ];

        return $output;
    }

    public function getCategoryTeamAttribute()
    {
        $type = null;
        $team = ArcheryEventMasterTeamCategory::where('id', $this->team_category_id)->first();
        if ($team) {
            $type = $team->type;
        }
        return $this->attributes['category_team'] = $type;
    }

    public function getLabelCategoryAttribute()
    {
        $label = "";
        $category =  ArcheryEventCategoryDetail::select(
            "archery_master_age_categories.label as label_age_categories",
            "archery_master_competition_categories.label as label_competition_categories",
            "archery_master_distances.label as label_distance",
            "archery_master_team_categories.label as label_team"
        )->join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_category_details.age_category_id')
            ->join('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_category_details.competition_category_id')
            ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_category_details.distance_id')
            ->join('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_category_details.team_category_id')
            ->where("archery_event_category_details.id", $this->id)
            ->first();

        if ($category) {
            $label = $category->label_competition_categories . " - " . $category->label_age_categories . " - " . $category->label_distance . " - " . $category->label_team;
        }
        return $this->attributes['label_category'] = $label;
    }

    public function getClassCategoryAttribute()
    {
        $class = "";
        $category =  ArcheryEventCategoryDetail::select(
            "archery_master_age_categories.label as label_age_categories",
            "archery_master_distances.label as label_distance"
        )->join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_category_details.age_category_id')
            ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_category_details.distance_id')
            ->where("archery_event_category_details.id", $this->id)
            ->first();

        if ($category) {
            $class = $category->label_age_categories . " - " . $category->label_distance;
        }
        return $this->attributes['class_category'] = $class;
    }

    public function getIsEarlyBirdAttribute()
    {
        $is_early_bird = 0;
        if (((int)$this->early_bird > 0) && ($this->end_date_early_bird != null)) {
            $carbon_early_bird_end_datetime = Carbon::parse($this->end_date_early_bird);
            $new_format_early_bird_end_datetime = Carbon::create($carbon_early_bird_end_datetime->year, $carbon_early_bird_end_datetime->month, $carbon_early_bird_end_datetime->day, 0, 0, 0);

            if (Carbon::today() <= $new_format_early_bird_end_datetime) {
                $is_early_bird = 1;
            }
        }
        return $this->attributes['is_early_bird'] = $is_early_bird;
    }

    public function getIsEarlyBirdWna()
    {
        $is_early_bird_wna = 0;
        if (((int)$this->early_price_wna > 0) && ($this->end_date_early_bird != null)) {
            $carbon_early_bird_end_datetime = Carbon::parse($this->end_date_early_bird);
            $new_format_early_bird_end_datetime = Carbon::create($carbon_early_bird_end_datetime->year, $carbon_early_bird_end_datetime->month, $carbon_early_bird_end_datetime->day, 0, 0, 0);

            if (Carbon::today() <= $new_format_early_bird_end_datetime) {
                $is_early_bird_wna = 1;
            }
        }
        return $is_early_bird_wna;
    }

    public function getGenderCategoryAttribute()
    {
        if ($this->team_category_id == 'individu male' || $this->team_category_id == 'male_team') {
            $gender = 'male';
        } else if ($this->team_category_id == 'individu female' || $this->team_category_id == 'female_team') {
            $gender = 'female';
        } else {
            $gender = 'mix';
        }

        return $gender;
    }

    public function getEventNameAttribute()
    {
        $event = ArcheryEvent::find($this->event_id);
        return $this->attributes['event_name'] = $event->event_name;
    }

    public function getStartEventAttribute()
    {
        $event =  ArcheryEvent::find($this->event_id);

        return $this->attributes['start_event'] = $event->event_start_datetime;
    }

    public function getEndEventAttribute()
    {
        $event =  ArcheryEvent::find($this->event_id);

        return $this->attributes['end_event'] = $event->event_end_datetime;
    }

    public static function getCategoriesRegisterEvent($event_id)
    {
        $is_marathon = 0;
        $event = ArcheryEvent::find($event_id);
        if (!$event) {
            throw new BLoCException("event not found");
        }

        if ($event->event_type == "Marathon") {
            $is_marathon = 1;
        }

        $datas = ArcheryEventCategoryDetail::select(
            'archery_event_category_details.id',
            'event_id',
            'age_category_id',
            'competition_category_id',
            'distance_id',
            'team_category_id',
            'quota',
            'archery_event_category_details.created_at',
            'archery_event_category_details.updated_at',
            'fee',
            'early_bird',
            "end_date_early_bird",
            "archery_master_team_categories.type",
            "archery_event_category_details.start_registration",
            "archery_event_category_details.end_registration",
            "archery_event_category_details.normal_price_wna",
            "archery_event_category_details.early_price_wna"
        )
            ->leftJoin('archery_master_team_categories', 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
            ->where('archery_event_category_details.event_id', $event_id)
            ->orderBy('archery_master_team_categories.short', 'asc')
            ->orderBy('archery_event_category_details.age_category_id', 'asc')
            ->orderBy('archery_event_category_details.competition_category_id', 'asc')
            ->get()->groupBy('team_category_id');

        foreach ($datas as $key => $team_categories) {
            foreach ($team_categories as $key => $category) {
                $count_participant = ArcheryEventParticipant::countEventUserBooking($category->id);
                $is_open = true;
                $range_date = [];
                if ($category->type == "Individual") {
                    $qualification_schedule = DB::table('archery_event_qualification_time')
                        ->where('category_detail_id', $category->id)->first();
                    if (!$qualification_schedule) {
                        $is_open = false;
                    } else {
                        if ($is_marathon == 1) {
                            // Start date
                            $start = strtotime($qualification_schedule->event_start_datetime);
                            // End date
                            $end = strtotime($qualification_schedule->event_end_datetime);

                            for ($i = $start; $i <= $end; $i += 86400) {
                                $day = date("Y-m-d", $i);
                                $range_date[] = $day;
                            }
                        }
                    }
                }

                $category->id = $category->id;
                $category->is_open = $is_open;
                $category->is_marathon = $is_marathon;

                $category->range_date = $range_date;
                $category->total_participant = $count_participant;
                $category->category_label = self::getCategoryLabel($category->id);

                $category_detail = ArcheryEventCategoryDetail::find($category->id);
                $age_config = [];
                if ($category_detail->is_age == 1) {
                    $age_config["is_age"] = $category_detail->is_age;
                    $age_config["min_age"] = $category_detail->min_age;
                    $age_config["max_age"] = $category_detail->max_age;
                } else {
                    $age_config["is_age"] = $category_detail->is_age;
                    $age_config["min_date_of_birth"] = $category_detail->min_date_of_birth;
                    $age_config["max_date_of_birth"] = $category_detail->max_date_of_birth;
                }

                $can_register = 0;
                if ($category_detail->start_registration && $category_detail->end_registration) {
                    if (
                        time() >= strtotime($category_detail->start_registration)
                        && time() <= strtotime($category_detail->end_registration)
                    ) {
                        $can_register = 1;
                    }
                } else {
                    if (
                        time() >= strtotime($event->registration_start_datetime)
                        && time() <= strtotime($event->registration_end_datetime)
                    ) {
                        $can_register = 1;
                    }
                }

                $category->can_register = $can_register;

                $category->is_early_bird_wna = $category_detail->getIsEarlyBirdWna();

                $category->age_config = $age_config;

                $category_team_detail = DB::table('archery_master_team_categories')->where('id', $category->team_category_id)->first();
                $category->team_category_detail = [
                    'id' => $category_team_detail->id,
                    'label' => $category_team_detail->label,
                ];
            }
        }
        return $datas;
    }

    private static function getCategoryLabel($id)
    {
        $category = DB::table('archery_event_category_details')
            ->join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_category_details.age_category_id')
            ->join('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_category_details.competition_category_id')
            ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_category_details.distance_id')
            ->select(
                "archery_master_age_categories.label as label_age_categories",
                "archery_master_competition_categories.label as label_competition_categories",
                "archery_master_distances.label as label_distance"
            )
            ->where('archery_event_category_details.id', $id)
            ->first();

        if (!$category) {
            return "";
        } else {
            return $category->label_age_categories . " - " . $category->label_competition_categories . " - " . $category->label_distance;
        }
    }

    public static function getCategoryLabelComplete($id)
    {
        $category = DB::table('archery_event_category_details')
            ->join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_category_details.age_category_id')
            ->join('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_category_details.competition_category_id')
            ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_category_details.distance_id')
            ->join('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_category_details.team_category_id')
            ->select(
                "archery_master_age_categories.label as label_age_categories",
                "archery_master_competition_categories.label as label_competition_categories",
                "archery_master_distances.label as label_distance",
                "archery_master_team_categories.label as label_team"
            )
            ->where('archery_event_category_details.id', $id)
            ->first();

        if (!$category) {
            return "";
        } else {
            return $category->label_age_categories . " - " . $category->label_competition_categories . " - " . $category->label_distance . " - " . $category->label_team;
        }
    }
}
