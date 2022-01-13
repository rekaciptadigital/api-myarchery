<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcheryEventCategoryDetail extends Model
{
    protected $table = 'archery_event_category_details';
    protected $guarded = ['id'];
    protected $appends = ['category_team'];
    const INDIVIDUAL_TYPE = "Individual";

    public function getCategoryTeamAttribute()
    {
        $team = ArcheryEventMasterTeamCategory::where('id', $this->team_category_id)->first();
        return $this->attributes['category_team'] = $team->type;
    }
}
