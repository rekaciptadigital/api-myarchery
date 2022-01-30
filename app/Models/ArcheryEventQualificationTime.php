<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ArcheryEventCategoryDetail;
use Illuminate\Support\Facades\DB;

class ArcheryEventQualificationTime extends Model
{
    protected $table = 'archery_event_qualification_time';

    protected function getQualificationById($event_id,$category_detail_id="")
    {
        $qualification = ArcheryEventCategoryDetail::select('event_id','category_detail_id','event_start_datetime','event_end_datetime','archery_event_qualification_time.id')
                         ->join("archery_event_qualification_time","archery_event_category_details.id","archery_event_qualification_time.category_detail_id")
                         ->where("archery_event_category_details.event_id",$event_id)
                         ->where(function ($query) use ($category_detail_id){
                            if(!empty($category_detail_id)){
                                $query->where("archery_event_qualification_time.category_detail_id",$category_detail_id);
                            }
                         })
                         ->get();

        return $qualification;
                
    }
}
