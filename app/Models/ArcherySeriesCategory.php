<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArcherySeriesCategory extends Model
{
    protected $table = 'archery_serie_categories';
    protected $guarded = ['id'];
    protected $appends = ['age_detail', 'competition_detail', 'distance_detail', 'team_detail', 'category_label'];

    public function getAgeDetailAttribute()
    {
        $age_category_detail = ArcheryMasterAgeCategory::find($this->age_category_id);
        return $this->attributes['age_detail'] = $age_category_detail ? $age_category_detail : null;
    }

    public function getCompetitionDetailAttribute()
    {
        $competition_category_detail = ArcheryMasterCompetitionCategory::find($this->competition_category_id);
        return $this->attributes['competition_detail'] = $competition_category_detail ? $competition_category_detail : null;
    }

    public function getDistanceDetailAttribute()
    {
        $distance_category_detail = ArcheryMasterDistanceCategory::find($this->distance_id);
        return $this->attributes['distance_detail'] = $distance_category_detail ? $distance_category_detail : null;
    }

    public function getTeamDetailAttribute()
    {
        $team_category_detail = ArcheryMasterTeamCategory::find($this->team_category_id);
        return $this->attributes['team_detail'] = $team_category_detail ? $team_category_detail : null;
    }

    public function getCategoryLabelAttribute()
    {
        $age_category_detail = ArcheryMasterAgeCategory::find($this->age_category_id);
        $competition_category_detail = ArcheryMasterCompetitionCategory::find($this->competition_category_id);
        $distance_category_detail = ArcheryMasterDistanceCategory::find($this->distance_id);
        $team_category_detail = ArcheryMasterTeamCategory::find($this->team_category_id);
        $category_label = "";
        if ($age_category_detail && $competition_category_detail && $distance_category_detail && $team_category_detail) {
            $category_label = $age_category_detail->label . "-" . $competition_category_detail->label . "-" . $distance_category_detail->label . "-" . $team_category_detail->label;
        }

        return $this->attributes['category_label'] = $category_label;
    }
}
