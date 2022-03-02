<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventSerie;
use App\Models\ArcheryEventCategoryDetail;
use App\Models\ArcheryEventParticipantMember;
use App\Models\ArcherySeriesCategory;
use App\Models\ArcherySeriesMasterPoint;

class ArcherySeriesUserPoint extends Model
{
    protected $table = 'archery_serie_user_point';
    protected $guarded = ['id'];

    // TODO : 
    protected function setPoint($user_id,$member_id,$category_id,$type,$pos){
        $category = ArcheryEventCategoryDetail::find($category_id);
        if(!$category) return false;

        $event_serie = ArcheryEventSerie::where("event_id",$category->event_id)->first();
        if(!$event_serie) return false;
        
        $member = ArcheryEventParticipantMember::find($member_id);
        if(!$member) return false;

        $archerySeriesCategory = ArcherySeriesCategory::where("age_category_id", $category->age_category_id)
        ->where("competition_category_id", $category->competition_category_id)
        ->where("distance_id", $category->distance_id)
        ->where("team_category_id", $category->team_category_id)
        ->where("serie_id", $event_serie->serie_id)
        ->first();;
        if(!$archerySeriesCategory) return false;

        $point = ArcherySeriesMasterPoint::where("serie_id",$event_serie->serie_id)->where("start_pos",">=",$pos)->where("end_pos","<=",$pos)->first();
        if(!$point) return false;

        // get detail event
        $this->create([
            "event_serie_id" => $event_serie->id,
            "user_id" => $user_id,
            "event_category_id" => $category_id,
            "point" => $point->point,
            "status" => $member->is_series,
            "type" => $type,
            "position" => $pos,
            "member_id" => $member_id,
        ]);
        // check event nya tegabung series
        // cari kategori tergabung ga dalam series
        // get member detail
        // get data master point
        // insert or update 
    }
}
