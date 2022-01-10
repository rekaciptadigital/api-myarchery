<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArcheryEventIdcardTemplate extends Model
{
    protected $table = 'archery_event_idcard_templates';

    public static function getCategoryLabel($participant_id, $user_id)
    {
        $category = DB::table('archery_event_participants')
                    ->join('archery_master_team_categories', 'archery_master_team_categories.id', '=', 'archery_event_participants.team_category_id')
                    ->join('archery_master_age_categories', 'archery_master_age_categories.id', '=', 'archery_event_participants.age_category_id')
                    ->join('archery_master_competition_categories', 'archery_master_competition_categories.id', '=', 'archery_event_participants.competition_category_id')
                    ->join('archery_master_distances', 'archery_master_distances.id', '=', 'archery_event_participants.distance_id')
                    ->select("archery_master_team_categories.label as label_team_categories",
                    "archery_master_age_categories.label as label_age_categories",
                    "archery_master_competition_categories.label as label_competition_categories",
                    "archery_master_distances.label as label_distance")
                    ->where('archery_event_participants.id', $participant_id)
                    ->where('archery_event_participants.user_id', $user_id)
                    ->first();

        if(!$category){
            return "";
        }else{
            return $category->label_team_categories." - ".$category->label_age_categories." - ".$category->label_competition_categories." - ".$category->label_distance;
        }
    }
}