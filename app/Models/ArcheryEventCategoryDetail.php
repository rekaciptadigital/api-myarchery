<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;

class ArcheryEventCategoryDetail extends Model
{
    protected $table = 'archery_event_category_details';
    protected $guarded = ['id'];
    protected $appends = ['category_team', 'max_age', 'event_name', 'gender_category'];
    const INDIVIDUAL_TYPE = "Individual";
    const TEAM_TYPE = "TEAM";

    public function getCategoryTeamAttribute()
    {
        $team = ArcheryEventMasterTeamCategory::where('id', $this->team_category_id)->first();
        return $this->attributes['category_team'] = $team->type;
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

    public static function getCategoriesRegisterEvent($event_id)
    {
        $datas = DB::table('archery_event_category_details')
            ->select('archery_event_category_details.id', 'event_id', 'age_category_id', 'competition_category_id', 'distance_id', 'team_category_id', 'quota', 'archery_event_category_details.created_at', 'archery_event_category_details.updated_at', 'fee')
            ->leftJoin('archery_master_team_categories', 'archery_master_team_categories.id', 'archery_event_category_details.team_category_id')
            ->where('archery_event_category_details.event_id', $event_id)
            ->orderBy('archery_master_team_categories.short', 'asc')->get()->groupBy('team_category_id');

        foreach ($datas as $key => $team_categories) {
            foreach ($team_categories as $key => $category) {
                $count_participant = ArcheryEventParticipant::where('event_id', $category->event_id)->where('event_category_id', $category->id)->count();
                $qualification_schedule = DB::table('archery_event_qualification_time')
                    ->where('category_detail_id', $category->id)->first();

                $category->id = $category->id;
                $category->is_open = !$qualification_schedule ? false : true;
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
}
