<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\ArcherySerieCity;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcheryEventParticipant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ArcheryEventCategoryDetail extends Model
{
    protected $table = 'archery_event_category_details';
    protected $guarded = ['id'];
    protected $appends = [
        'category_team', 'max_age', 'event_name', 'gender_category', 'min_age', 'start_event',
        'is_early_bird', 'label_category', 'class_category'
    ];
    const INDIVIDUAL_TYPE = "Individual";
    const TEAM_TYPE = "Team";

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

        $can_update_series = 0;
        if (Carbon::now() <  $this->start_event && $have_series == 1) {
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
        if (($this->early_bird > 0) && ($this->end_date_early_bird != null)) {
            $carbon_early_bird_end_datetime = Carbon::parse($this->end_date_early_bird);
            $new_format_early_bird_end_datetime = Carbon::create($carbon_early_bird_end_datetime->year, $carbon_early_bird_end_datetime->month, $carbon_early_bird_end_datetime->day, 0, 0, 0);

            if (Carbon::today() <= $new_format_early_bird_end_datetime) {
                $is_early_bird = 1;
            }
        }
        return $this->attributes['is_early_bird'] = $is_early_bird;
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

    public function getMaxAgeAttribute()
    {
        $age = ArcheryEventMasterAgeCategory::where('id', $this->age_category_id)->first();
        if (!$age) {
            return $this->attributes['max_age'] = 0;
        }
        return $this->attributes['max_age'] = $age->max_age;
    }

    public function getStartEventAttribute()
    {
        $event =  ArcheryEvent::find($this->event_id);

        return $this->attributes['start_event'] = $event->event_start_datetime;
    }

    public function getMinAgeAttribute()
    {
        $age = ArcheryEventMasterAgeCategory::where('id', $this->age_category_id)->first();
        if (!$age) {
            return $this->attributes['min_age'] = 0;
        }
        return $this->attributes['min_age'] = $age->min_age;
    }

    public static function getCategoriesRegisterEvent($event_id)
    {
        $datas = ArcheryEventCategoryDetail
            ::select('archery_event_category_details.id', 'event_id', 'age_category_id', 'competition_category_id', 'distance_id', 'team_category_id', 'quota', 'archery_event_category_details.created_at', 'archery_event_category_details.updated_at', 'fee', 'early_bird', "end_date_early_bird", "archery_master_team_categories.type")
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
                if ($category->type == "Individual") {
                    $qualification_schedule = DB::table('archery_event_qualification_time')
                        ->where('category_detail_id', $category->id)->first();
                    if (!$qualification_schedule) {
                        $is_open = false;
                    }
                }

                $category->id = $category->id;
                $category->is_open = $is_open;
                $category->total_participant = $count_participant;
                $category->category_label = self::getCategoryLabel($category->id);

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
