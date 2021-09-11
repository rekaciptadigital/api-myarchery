<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArcheryEvent extends Model
{
    protected $appends = ['event_url', 'flat_categories'];

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

    public function getPosterAttribute($value)
    {
        return $value ? route('api_display', ['file_path' => $value]) : $value;
    }

    public function getHandbookAttribute($value)
    {

        return $value ? route('api_download', ['file_path' => $value]) : $value;
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

    public function getEventUrlAttribute()
    {
        return env('WEB_DOMAIN', 'https://my-archery.id') . '/' . Str::slug($this->admin->name) . '/' . $this->event_slug;
    }

    public function getFlatCategoriesAttribute()
    {
        $query = "
            SELECT A.id as archery_event_id,
                B.age_category_id, B.age_category_label,
                C.competition_category_id, C.competition_category_label,
                D.team_category_id, D.team_category_label,
                E.distance_id, E.distance_label,
                CONCAT(D.team_category_label, ' - ', B.age_category_label, ' - ', C.competition_category_label, ' - ', E.distance_label) as archery_event_category_label
            FROM archery_events A
            JOIN archery_event_categories B ON A.id = B.event_id
            JOIN archery_event_category_competitions C ON B.id = C.event_category_id
            JOIN archery_event_category_competition_teams D ON C.id = D.event_category_competition_id
            JOIN archery_event_category_competition_distances E ON C.id = E.event_category_competition_id
            WHERE A.id = :event_id
            ORDER BY D.team_category_label, B.age_category_label, C.competition_category_label, E.distance_label
        ";

        $results = DB::select($query, ['event_id' => $this->id]);

        return $results;
    }
}
